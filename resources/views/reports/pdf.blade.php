<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; color: #1a56db; }
        .header p { margin: 5px 0 0; font-size: 14px; color: #666; }
        
        .summary { margin-bottom: 30px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; }
        .summary .label { font-size: 11px; color: #64748b; display: block; margin-bottom: 5px; }
        .summary .value { font-size: 16px; font-weight: bold; color: #0f172a; }
        .summary .value.green { color: #16a34a; }
        .summary .value.red { color: #dc2626; }
        .summary .value.blue { color: #2563eb; }
        
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; margin-top: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; }
        
        table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.data th, table.data td { padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        table.data th { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-right { text-align: right !important; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 10px; border-radius: 4px; background: #e2e8f0; }
        
        .breakdown { margin-bottom: 20px; display: table; width: 100%; }
        .breakdown-item { display: table-cell; width: 50%; vertical-align: top; padding-right: 15px; }
        .breakdown-item:last-child { padding-right: 0; padding-left: 15px; }
        
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RenzBilliard</h1>
        <p>Laporan Keuangan</p>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($data['period']['start'])->format('d M Y') }}</strong> - <strong>{{ \Carbon\Carbon::parse($data['period']['end'])->format('d M Y') }}</strong></p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Total Pemasukan</span>
                <span class="value green">Rp {{ number_format($data['summary']['revenue'], 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Total Pengeluaran</span>
                <span class="value red">Rp {{ number_format($data['summary']['expenses'] ?? 0, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Laba Bersih</span>
                <span class="value blue">Rp {{ number_format($data['summary']['profit'] ?? 0, 0, ',', '.') }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Total Transaksi</span>
                <span class="value">{{ $data['summary']['transactions'] }}</span>
            </td>
            <td>
                <span class="label">Total Sesi Billiard</span>
                <span class="value">{{ $data['summary']['sessions'] }}</span>
            </td>
            <td>
                <span class="label">Total Pengeluaran</span>
                <span class="value">{{ $data['summary']['expense_count'] ?? 0 }}</span>
            </td>
        </tr>
    </table>
    
    <div class="breakdown">
        <div class="breakdown-item">
            <div class="section-title">Sumber Pendapatan</div>
            <table class="data">
                <tr>
                    <td>🎱 Sewa Meja (Billiard)</td>
                    <td class="text-right">Rp {{ number_format($data['breakdown']['billiard'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>🍔 F&B / Produk</td>
                    <td class="text-right">Rp {{ number_format($data['breakdown']['fnb'], 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        <div class="breakdown-item">
            <div class="section-title">Kategori Pengeluaran</div>
            <table class="data">
                @php
                    $categoryLabels = [
                        'operasional' => 'Operasional',
                        'gaji' => 'Gaji',
                        'pembelian_stok' => 'Pembelian Stok',
                        'lainnya' => 'Lainnya'
                    ];
                @endphp
                @forelse($data['expense_breakdown'] ?? [] as $category => $amount)
                <tr>
                    <td>{{ $categoryLabels[$category] ?? $category }}</td>
                    <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #888;">Tidak ada pengeluaran</td>
                </tr>
                @endforelse
            </table>
        </div>
    </div>

    <div class="breakdown">
        <div class="breakdown-item">
             <div class="section-title">Metode Pembayaran</div>
             <table class="data">
                 @foreach($data['payment_methods'] as $pm)
                 <tr>
                     <td style="text-transform: capitalize">{{ $pm->payment_method === 'cash' ? 'Tunai' : strtoupper($pm->payment_method) }}</td>
                     <td class="text-right">{{ $pm->count }}x</td>
                     <td class="text-right">Rp {{ number_format($pm->total, 0, ',', '.') }}</td>
                 </tr>
                 @endforeach
             </table>
        </div>
    </div>

    <div class="section-title">🎱 Transaksi Billiard</div>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Meja</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['billiard_items'] as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['date']->format('d/m/Y H:i') }}</td>
                <td>{{ $item['invoice'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 10px; color: #888;">Tidak ada transaksi billiard</td>
            </tr>
            @endforelse
            @if(count($data['billiard_items']) > 0)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($data['breakdown']['billiard'], 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="section-title" style="margin-top: 20px;">🍔 Transaksi F&B</div>
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Item</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['fnb_items'] as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['date']->format('d/m/Y H:i') }}</td>
                <td>{{ $item['invoice'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 10px; color: #888;">Tidak ada transaksi F&B</td>
            </tr>
            @endforelse
            @if(count($data['fnb_items']) > 0)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right">Rp {{ number_format($data['breakdown']['fnb'], 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    <div class="footer">
        Dicetak pada: {{ now()->format('d M Y H:i') }} | RenzBilliard Billing System
    </div>
</body>
</html>
