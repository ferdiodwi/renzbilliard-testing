<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableBilliard;
use App\Models\Rate;
use App\Models\SessionBilliard;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Get active sessions.
     */
    public function active(): JsonResponse
    {
        $sessions = SessionBilliard::with(['table', 'rate'])
            ->active()
            ->orderBy('end_time')
            ->get()
            ->map(function ($session) {
                $data = [
                    'id' => $session->id,
                    'table' => [
                        'id' => $session->table->id,
                        'table_number' => $session->table->table_number,
                    ],
                    'rate' => [
                        'id' => $session->rate->id,
                        'name' => $session->rate->name,
                        'price_per_hour' => $session->rate->price_per_hour,
                    ],
                    'start_time' => $session->start_time->toIso8601String(),
                    'is_open_billing' => $session->is_open_billing,
                    'auto_stop' => $session->auto_stop,
                    'is_paid' => $session->transactionItem()->exists(),
                ];

                if ($session->is_open_billing) {
                    // Open billing: no end time, show elapsed and current estimate
                    $data['end_time'] = null;
                    $data['duration_minutes'] = null;
                    $data['remaining_seconds'] = -1; // Unlimited
                    $data['elapsed_minutes'] = $session->elapsed_minutes;
                    $data['elapsed_seconds'] = $session->elapsed_seconds;
                    $data['total_price'] = 0; // TBD when stopped
                    $data['current_price_estimate'] = $session->getCurrentPriceEstimate();
                } else {
                    // Closed billing: show end time and remaining
                    $data['end_time'] = $session->end_time->toIso8601String();
                    $data['duration_minutes'] = $session->duration_minutes;
                    $data['remaining_seconds'] = $session->remaining_seconds;
                    $data['total_price'] = $session->total_price;
                }

                return $data;
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Start a new session.
     */
    public function start(Request $request): JsonResponse
    {
        // Validate is_open_billing first
        $isOpenBilling = $request->boolean('is_open_billing', false);
        
        $rules = [
            'table_id' => 'required|exists:tables_billiard,id',
            'customer_name' => 'required|string|max:255',
            'rate_id' => 'required|exists:rates,id',
            'is_open_billing' => 'boolean',
            'auto_stop' => 'boolean',
        ];
        
        // Duration is required only for closed billing
        if (!$isOpenBilling) {
            $rules['duration_minutes'] = 'required|integer|min:2|max:480';
        }
        
        $request->validate($rules);

        $table = TableBilliard::findOrFail($request->table_id);

        if (!$table->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak tersedia',
            ], 422);
        }
        
        // STRICT RESERVATION POLICY: Check if table has active booking that overlaps with session time
        $sessionStartTime = Carbon::now();
        $sessionEndTime = $isOpenBilling 
            ? Carbon::now()->addHours(8) // For open billing, assume max 8 hours
            : Carbon::now()->addMinutes($request->duration_minutes);
        
        $conflictingBooking = \App\Models\Booking::where('table_id', $table->id)
            ->where('status', 'PENDING') // Only pending bookings (not started yet, not cancelled)
            ->where(function($q) use ($sessionStartTime, $sessionEndTime) {
                // Check if booking time overlaps with session time
                $q->where('start_time', '<', $sessionEndTime)
                  ->where('end_time', '>', $sessionStartTime);
            })
            ->first();
        
        if ($conflictingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Meja sudah di-booking untuk jam ' . 
                             $conflictingBooking->start_time->format('H:i') . ' - ' .
                             $conflictingBooking->end_time->format('H:i') . 
                             ' atas nama ' . $conflictingBooking->customer_name,
                'booking' => [
                    'customer_name' => $conflictingBooking->customer_name,
                    'start_time' => $conflictingBooking->start_time->format('H:i'),
                    'end_time' => $conflictingBooking->end_time->format('H:i'),
                ],
            ], 422);
        }


        $rate = Rate::findOrFail($request->rate_id);
        $startTime = Carbon::now();
        $isOpenBilling = $request->boolean('is_open_billing', false);
        
        // Set values based on billing mode
        if ($isOpenBilling) {
            $endTime = null;
            $durationMinutes = null;
            $totalPrice = 0; // Will be calculated when stopped
        } else {
            $endTime = $startTime->copy()->addMinutes($request->duration_minutes);
            $durationMinutes = $request->duration_minutes;
            $totalPrice = $rate->calculatePrice($request->duration_minutes);
        }

        DB::beginTransaction();
        try {
            $session = SessionBilliard::create([
                'table_id' => $table->id,
                'customer_name' => $request->customer_name,
                'rate_id' => $rate->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $durationMinutes,
                'total_price' => $totalPrice,
                'auto_stop' => $request->auto_stop ?? true,
                'is_open_billing' => $isOpenBilling,
                'status' => 'playing',
            ]);

            $table->update(['status' => 'playing']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dimulai',
                'data' => [
                    'id' => $session->id,
                    'table_number' => $table->table_number,
                    'rate_name' => $rate->name,
                    'start_time' => $session->start_time->toIso8601String(),
                    'end_time' => $session->end_time?->toIso8601String(),
                    'duration_minutes' => $session->duration_minutes,
                    'total_price' => $session->total_price,
                    'is_open_billing' => $session->is_open_billing,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai sesi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop a session manually.
     */
    public function stop(Request $request, $id)
    {
        $session = SessionBilliard::with(['table', 'rate'])->findOrFail($id);

        if ($session->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi sudah selesai',
            ], 422);
        }

        $now = Carbon::now();
        
        if ($session->is_open_billing) {
            // Open billing: calculate from start to now
            $endTime = $now;
            $actualDuration = $session->start_time->diffInMinutes($endTime);
            
            // Minimum 2 hours (120 minutes) charge for open billing
            $chargeableDuration = max(120, $actualDuration);
            $sessionPrice = $session->rate->calculatePrice($chargeableDuration);
        } else {
            // Closed billing: pay for full booked duration regardless of when stopped
            $endTime = $now;
            
            // If stopped after scheduled end time, cap at scheduled end time
            if ($session->end_time && $now->greaterThan($session->end_time)) {
                $endTime = $session->end_time;
            }
            
            $actualDuration = $session->start_time->diffInMinutes($endTime);
            
            // Use ORIGINAL price (full duration), no recalculation
            // Customer pays for booked time even if they stop early
            $sessionPrice = $session->total_price;
            
            // Debug logging
            \Log::info('Closed Billing Stop', [
                'session_id' => $session->id,
                'original_duration' => $session->duration_minutes,
                'original_price' => $session->total_price,
                'actual_duration' => $actualDuration,
                'final_price' => $sessionPrice,
            ]);
        }

        // Check if already paid (Prepaid)
        $isPrepaid = $session->transactionItem()->exists();
        if ($isPrepaid) {
            $sessionPrice = 0; // Already paid, so nothing to pay now
        }

        // Get F&B orders linked to this session
        $fnbOrders = Order::where('session_id', $session->id)
            ->whereIn('status', ['pending', 'completed'])
            ->get();
        
        $fnbTotal = $fnbOrders->sum('total');

        DB::beginTransaction();
        try {
            // Update session
            $updateData = [
                'end_time' => $endTime,
                'status' => 'finished',
            ];
            
            // For open billing, update duration to actual and set total price
            // For closed billing, keep original duration and price
            if ($session->is_open_billing) {
                $updateData['duration_minutes'] = $actualDuration;
                $updateData['total_price'] = $sessionPrice;
            }
            
            $session->update($updateData);

            // Auto-complete booking if this session was from a booking
            $booking = \App\Models\Booking::where('session_id', $session->id)
                ->where('status', 'PLAYING')
                ->first();
            
            if ($booking) {
                $booking->update(['status' => 'COMPLETED']);
            }

            // Update table status
            $session->table->update(['status' => 'available']);

            DB::commit();
            
            // Refresh session to get updated data
            $session->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dihentikan',
                'data' => [
                    'session' => $session,
                    'session_charges' => $sessionPrice,
                    'fnb_charges' => $fnbTotal,
                    'total_charges' => $sessionPrice + $fnbTotal,
                    'fnb_orders' => $fnbOrders->load('items.product'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan sesi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extend a session.
     */
    public function extend(Request $request, SessionBilliard $session): JsonResponse
    {
        $request->validate([
            'additional_minutes' => 'required|integer|min:15|max:240',
        ]);

        if ($session->status !== 'playing') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi sudah selesai',
            ], 422);
        }
        
        // Cannot extend open billing sessions
        if ($session->is_open_billing) {
            return response()->json([
                'success' => false,
                'message' => 'Open billing tidak bisa diperpanjang. Sesi akan berjalan hingga Anda menghentikannya.',
            ], 422);
        }

        $additionalMinutes = $request->additional_minutes;
        $additionalPrice = $session->rate->calculatePrice($additionalMinutes);

        $session->update([
            'end_time' => $session->end_time->addMinutes($additionalMinutes),
            'duration_minutes' => $session->duration_minutes + $additionalMinutes,
            'total_price' => $session->total_price + $additionalPrice,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diperpanjang',
            'data' => [
                'id' => $session->id,
                'new_end_time' => $session->end_time?->toIso8601String(),
                'new_duration_minutes' => $session->duration_minutes,
                'new_total_price' => $session->total_price,
            ],
        ]);
    }

    /**
     * Delete a session (Cancel).
     */
    public function destroy(SessionBilliard $session): JsonResponse
    {
        if ($session->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi yang sudah selesai tidak dapat dibatalkan',
            ], 422);
        }

        if ($session->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi yang memiliki pesanan tidak dapat dibatalkan',
            ], 422);
        }

        if ($session->transactionItem()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi yang sudah dibayar tidak dapat dibatalkan',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore table status
            $session->table->update(['status' => 'available']);
            
            // Delete session
            $session->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dibatalkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan sesi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session details.
     */
    public function show(SessionBilliard $session): JsonResponse
    {
        $session->load(['table', 'rate']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'table' => [
                    'id' => $session->table->id,
                    'table_number' => $session->table->table_number,
                ],
                'rate' => [
                    'id' => $session->rate->id,
                    'name' => $session->rate->name,
                    'price_per_hour' => $session->rate->price_per_hour,
                ],
                'start_time' => $session->start_time->toIso8601String(),
                'end_time' => $session->end_time?->toIso8601String(),
                'duration_minutes' => $session->duration_minutes,
                'remaining_seconds' => $session->remaining_seconds,
                'total_price' => $session->total_price,
                'status' => $session->status,
                'auto_stop' => $session->auto_stop,
            ],
        ]);
    }
}
