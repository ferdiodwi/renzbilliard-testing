<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableBilliard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of tables.
     */
    public function index(): JsonResponse
    {
        $tables = TableBilliard::with(['activeSession.rate', 'activeSession.orders'])->get();

        $data = $tables->map(function ($table) {
            $result = [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'status' => $table->status,
            ];

            if ($table->activeSession) {
                // Calculate F&B total for pending/completed orders
                $activeOrders = $table->activeSession->orders
                    ->whereIn('status', ['pending', 'completed']);
                
                $fnbTotal = $activeOrders->sum('total');
                
                // Aggregate items for display: "Mie(2), Kopi(1)"
                $allItems = collect();
                foreach ($activeOrders as $order) {
                    foreach ($order->items as $item) {
                        $existing = $allItems->firstWhere('product_id', $item->product_id);
                        if ($existing) {
                            $existing['quantity'] += $item->quantity;
                        } else {
                            $allItems->push([
                                'product_id' => $item->product_id,
                                'name' => $item->product->name,
                                'quantity' => $item->quantity
                            ]);
                        }
                    }
                }
                
                $fnbSummary = $allItems->map(function($item) {
                    return "{$item['name']} ({$item['quantity']})";
                })->implode(', ');

                $result['active_session'] = [
                    'id' => $table->activeSession->id,
                    'customer_name' => $table->activeSession->customer_name,
                    'start_time' => $table->activeSession->start_time->toIso8601String(),
                    'end_time' => $table->activeSession->end_time?->toIso8601String(),
                    'remaining_seconds' => $table->activeSession->remaining_seconds,
                    'duration_minutes' => $table->activeSession->duration_minutes,
                    'elapsed_minutes' => $table->activeSession->elapsed_minutes,
                    'elapsed_seconds' => $table->activeSession->elapsed_seconds,
                    'is_open_billing' => $table->activeSession->is_open_billing,
                    'rate' => [
                        'name' => $table->activeSession->rate->name,
                        'price_per_hour' => $table->activeSession->rate->price_per_hour,
                    ],
                    'session_price' => $table->activeSession->total_price,
                    'fnb_total' => $fnbTotal,
                    'fnb_summary' => $fnbSummary,
                    'total_price' => $table->activeSession->total_price + $fnbTotal,
                    'current_price_estimate' => $table->activeSession->getCurrentPriceEstimate(),
                    'is_paid' => $table->activeSession->transactionItem()->exists(),
                ];
            }
            
            // Check for next booking today (for conflict prevention)
            if ($table->status === 'available' || $table->status === 'playing') {
                $nextBooking = \App\Models\Booking::where('table_id', $table->id)
                    ->whereDate('booking_date', today())
                    ->whereIn('status', ['PENDING', 'CONFIRMED'])
                    ->where('start_time', '>', now())
                    ->orderBy('start_time')
                    ->first(['start_time', 'customer_name']);
                
                if ($nextBooking) {
                    $minutesUntilBooking = now()->diffInMinutes($nextBooking->start_time);
                    
                    $result['next_booking'] = [
                        'start_time' => $nextBooking->start_time->format('H:i'),
                        'customer_name' => $nextBooking->customer_name,
                    ];
                    
                    // Max duration is minutes until booking minus 10 min buffer
                    $result['max_duration_minutes'] = max(0, $minutesUntilBooking - 10);
                }
            }

            return $result;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'table_number' => 'required|string|max:10|unique:tables_billiard,table_number',
        ]);

        $table = TableBilliard::create([
            'table_number' => $request->table_number,
            'status' => 'available',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil ditambahkan',
            'data' => $table,
        ], 201);
    }

    /**
     * Display the specified table.
     */
    public function show(TableBilliard $table): JsonResponse
    {
        $table->load(['activeSession.rate']);

        return response()->json([
            'success' => true,
            'data' => $table,
        ]);
    }

    /**
     * Update the specified table.
     */
    public function update(Request $request, TableBilliard $table): JsonResponse
    {
        $request->validate([
            'table_number' => 'sometimes|string|max:10|unique:tables_billiard,table_number,' . $table->id,
            'status' => 'sometimes|in:available,maintenance',
        ]);

        // Cannot change status if table is playing
        if ($table->isPlaying() && $request->has('status')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah status meja yang sedang bermain',
            ], 422);
        }

        $table->update($request->only(['table_number', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil diupdate',
            'data' => $table,
        ]);
    }

    /**
     * Remove the specified table.
     */
    public function destroy(TableBilliard $table): JsonResponse
    {
        if ($table->isPlaying()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus meja yang sedang bermain',
            ], 422);
        }

        // Check if table has sessions
        if ($table->sessions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus meja yang memiliki riwayat sesi',
            ], 422);
        }

        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil dihapus',
        ]);
    }
}
