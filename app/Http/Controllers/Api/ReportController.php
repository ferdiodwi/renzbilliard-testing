<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\SessionBilliard;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get report statistics (API).
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getReportData($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export report to PDF or Excel (CSV).
     */
    public function export(Request $request)
    {
        $data = $this->getReportData($request);
        $type = $request->query('type', 'pdf');
        $startDate = Carbon::parse($data['period']['start'])->format('d M Y');
        $endDate = Carbon::parse($data['period']['end'])->format('d M Y');
        $fileName = "Laporan_RenzBilliard_{$startDate}-{$endDate}";

        if ($type === 'excel') {
            return $this->exportCsv($data, $fileName);
        }

        // PDF download
        $pdf = Pdf::loadView('reports.pdf', ['data' => $data]);
        return $pdf->download("{$fileName}.pdf");
    }

    /**
     * Shared logic to fetch report data.
     */
    private function getReportData(Request $request): array
    {
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date'))->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date'))->endOfDay() 
            : Carbon::now()->endOfDay();

        // 1. Income Summary (from Transactions)
        $totalRevenue = Transaction::whereBetween('paid_at', [$startDate, $endDate])->sum('total_amount');
        $totalSessions = SessionBilliard::whereBetween('start_time', [$startDate, $endDate])->count();
        $totalTransactions = Transaction::whereBetween('paid_at', [$startDate, $endDate])->count();

        // 2. Expense Summary
        $totalExpenses = Expense::whereBetween('expense_date', [
            $startDate->toDateString(), 
            $endDate->toDateString()
        ])->sum('amount');
        
        $expenseCount = Expense::whereBetween('expense_date', [
            $startDate->toDateString(), 
            $endDate->toDateString()
        ])->count();

        // Expense by category
        $expenseByCategory = Expense::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category')
            ->toArray();

        // 3. Profit/Loss
        $netProfit = $totalRevenue - $totalExpenses;

        // 4. Daily Chart Data
        $transactions = Transaction::select(
            DB::raw("DATE_FORMAT(paid_at, '%Y-%m-%d') as date"),
            DB::raw('SUM(total_amount) as total')
        )
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $expenseChart = Expense::select(
            'expense_date as date',
            DB::raw('SUM(amount) as total')
        )
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('expense_date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $key = $current->format('Y-m-d');
            $income = (float) ($transactions[$key]->total ?? 0);
            $expense = (float) ($expenseChart[$key]->total ?? 0);
            $chartData[] = [
                'date' => $key,
                'label' => $current->format('d M'),
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
            $current->addDay();
        }

        // 5. Transactions List (removed - replaced with separated items below)
        
        // 6. Revenue Breakdown (Billiard vs FnB)
        $revenueByCategory = [
            'billiard' => 0,
            'fnb' => 0
        ];
        
        // Get Billiard items (type = 'session')
        $billiardItems = TransactionItem::with(['transaction.cashier', 'session.table'])
            ->where('type', 'session')
            ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_at', [$startDate, $endDate]);
            })
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->transaction->paid_at,
                    'invoice' => $item->transaction->invoice_number,
                    'description' => 'Meja ' . ($item->session->table->table_number ?? '?'),
                    'customer' => $item->session->customer_name ?? '-',
                    'payment_method' => $item->transaction->payment_method,
                    'cashier' => $item->transaction->cashier->name ?? '-',
                    'amount' => $item->price,
                ];
            });

        // Get F&B items (type = 'product')
        $fnbItems = TransactionItem::with(['transaction.cashier', 'product', 'session'])
            ->where('type', 'product')
            ->whereHas('transaction', function($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_at', [$startDate, $endDate]);
            })
            ->get()
            ->map(function($item) {
                $qty = $item->quantity ?? 1;
                $date = $item->transaction->paid_at->format('Ymd');
                return [
                    'id' => $item->id,
                    'date' => $item->transaction->paid_at,
                    'invoice' => sprintf('FNB-%s-%04d', $date, $item->id),
                    'description' => ($item->product->name ?? 'Produk') . ' x' . $qty,
                    'customer' => $item->session->customer_name ?? '-',
                    'payment_method' => $item->transaction->payment_method,
                    'cashier' => $item->transaction->cashier->name ?? '-',
                    'amount' => $item->price,
                ];
            });

        // Calculate totals from items
        $revenueByCategory['billiard'] = $billiardItems->sum('amount');
        $revenueByCategory['fnb'] = $fnbItems->sum('amount');

        // 7. Payment Method Stats
        $paymentStats = Transaction::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        return [
            'summary' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'profit' => $netProfit,
                'sessions' => $totalSessions,
                'transactions' => $totalTransactions,
                'expense_count' => $expenseCount,
                'billiard_count' => $billiardItems->count(),
                'fnb_count' => $fnbItems->count(),
            ],
            'chart' => $chartData,
            'billiard_items' => $billiardItems->values(),
            'fnb_items' => $fnbItems->values(),
            'breakdown' => $revenueByCategory,
            'expense_breakdown' => $expenseByCategory,
            'payment_methods' => $paymentStats,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ]
        ];
    }

    private function exportCsv($data, $fileName)
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Summary Section
            fputcsv($file, ['=== RINGKASAN LAPORAN ===']);
            fputcsv($file, ['Periode', $data['period']['start'] . ' s/d ' . $data['period']['end']]);
            fputcsv($file, []);
            fputcsv($file, ['Total Pemasukan', 'Rp ' . number_format($data['summary']['revenue'], 0, ',', '.')]);
            fputcsv($file, ['Total Pengeluaran', 'Rp ' . number_format($data['summary']['expenses'], 0, ',', '.')]);
            fputcsv($file, ['Laba Bersih', 'Rp ' . number_format($data['summary']['profit'], 0, ',', '.')]);
            fputcsv($file, []);
            
            // Income Breakdown
            fputcsv($file, ['=== SUMBER PENDAPATAN ===']);
            fputcsv($file, ['Billiard', 'Rp ' . number_format($data['breakdown']['billiard'], 0, ',', '.')]);
            fputcsv($file, ['F&B', 'Rp ' . number_format($data['breakdown']['fnb'], 0, ',', '.')]);
            fputcsv($file, []);
            
            // Expense Breakdown
            fputcsv($file, ['=== KATEGORI PENGELUARAN ===']);
            $categoryLabels = [
                'operasional' => 'Operasional',
                'gaji' => 'Gaji',
                'pembelian_stok' => 'Pembelian Stok',
                'lainnya' => 'Lainnya'
            ];
            foreach ($data['expense_breakdown'] as $category => $amount) {
                fputcsv($file, [$categoryLabels[$category] ?? $category, 'Rp ' . number_format($amount, 0, ',', '.')]);
            }
            fputcsv($file, []);
            
            // Billiard Transactions
            fputcsv($file, ['=== TRANSAKSI BILLIARD ===']);
            fputcsv($file, ['No', 'Tanggal', 'Invoice', 'Meja', 'Jumlah']);
            
            foreach ($data['billiard_items'] as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item['date']->format('Y-m-d H:i'),
                    $item['invoice'],
                    $item['description'],
                    'Rp ' . number_format($item['amount'], 0, ',', '.')
                ]);
            }
            fputcsv($file, []);

            // F&B Transactions
            fputcsv($file, ['=== TRANSAKSI F&B ===']);
            fputcsv($file, ['No', 'Tanggal', 'Invoice', 'Item', 'Jumlah']);
            
            foreach ($data['fnb_items'] as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item['date']->format('Y-m-d H:i'),
                    $item['invoice'],
                    $item['description'],
                    'Rp ' . number_format($item['amount'], 0, ',', '.')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
