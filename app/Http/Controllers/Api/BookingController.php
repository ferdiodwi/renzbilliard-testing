<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TableBilliard;
use App\Models\Rate;
use App\Models\SessionBilliard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index(Request $request)
    {
        $query = Booking::with(['table', 'rate', 'session']);

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by table
        if ($request->has('table_id')) {
            $query->where('table_id', $request->table_id);
        }

        // Search by customer name or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('start_time', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables_billiard,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:30',
            'rate_id' => 'required|exists:rates,id',
            'notes' => 'nullable|string',
        ]);

        // Combine booking_date with start_time
        $startTime = Carbon::parse($request->booking_date . ' ' . $request->start_time);
        $endTime = $startTime->copy()->addMinutes($request->duration_minutes);

        // Validate: booking cannot be in the past
        if ($startTime->lt(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu booking tidak boleh di masa lalu',
            ], 422);
        }

        // Check table availability
        if (!Booking::isTableAvailable($request->table_id, $startTime, $endTime)) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak tersedia pada waktu tersebut. Ada booking lain yang bentrok.',
            ], 422);
        }

        // Calculate estimated price
        $rate = Rate::findOrFail($request->rate_id);
        $estimatedPrice = $rate->calculatePrice($request->duration_minutes);

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'table_id' => $request->table_id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'booking_date' => $request->booking_date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $request->duration_minutes,
                'rate_id' => $request->rate_id,
                'estimated_price' => $estimatedPrice,
                'notes' => $request->notes,
                'status' => 'PENDING',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat',
                'data' => $booking->load(['table', 'rate']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified booking
     */
    public function show($id)
    {
        $booking = Booking::with(['table', 'rate', 'session'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Cannot update if already playing, completed, or cancelled
        if (in_array($booking->status, ['PLAYING', 'COMPLETED', 'CANCELLED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak bisa diubah (status: ' . $booking->status . ')',
            ], 422);
        }

        $request->validate([
            'customer_name' => 'sometimes|string|max:255',
            'customer_phone' => 'sometimes|string|max:20',
            'booking_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'duration_minutes' => 'sometimes|integer|min:30',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // If date or time is being updated, recalculate timestamps and check availability
            if ($request->has('booking_date') || $request->has('start_time') || $request->has('duration_minutes')) {
                $bookingDate = $request->booking_date ?? Carbon::parse($booking->booking_date)->format('Y-m-d');
                $startTimeString = $request->start_time ?? Carbon::parse($booking->start_time)->format('H:i');
                
                $startTime = Carbon::parse($bookingDate . ' ' . $startTimeString);
                $duration = $request->duration_minutes ?? $booking->duration_minutes;
                $endTime = $startTime->copy()->addMinutes((int) $duration);

                // Check availability (exclude current booking)
                if (!Booking::isTableAvailable($booking->table_id, $startTime, $endTime, $booking->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Waktu baru bentrok dengan booking lain',
                    ], 422);
                }

                $booking->booking_date = $bookingDate;
                $booking->start_time = $startTime;
                $booking->end_time = $endTime;
                $booking->duration_minutes = $duration;
                
                // Recalculate price
                $booking->estimated_price = $booking->rate->calculatePrice($duration);
            }

            // Update other fields
            if ($request->has('customer_name')) {
                $booking->customer_name = $request->customer_name;
            }
            if ($request->has('customer_phone')) {
                $booking->customer_phone = $request->customer_phone;
            }
            if ($request->has('notes')) {
                $booking->notes = $request->notes;
            }

            $booking->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil diupdate',
                'data' => $booking->load(['table', 'rate']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);

        // Cannot cancel if already playing or completed
        if (in_array($booking->status, ['PLAYING', 'COMPLETED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak bisa dibatalkan (sudah ' . strtolower($booking->status) . ')',
            ], 422);
        }

        $booking->update(['status' => 'CANCELLED']);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan',
        ]);
    }

    /**
     * Check-in a booking (create session)
     */
    public function checkIn($id)
    {
        $booking = Booking::with(['table', 'rate'])->findOrFail($id);

        if (!$booking->isCheckInAllowed()) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak bisa di-check-in (status: ' . $booking->status . ')',
            ], 422);
        }

        // PREVENT EARLY CHECK-IN: Check-in hanya boleh pada atau setelah waktu booking
        if (now()->lt($booking->start_time)) {
            $waitMinutes = (int) now()->diffInMinutes($booking->start_time);
            return response()->json([
                'success' => false,
                'message' => 'Belum waktunya check-in. Check-in bisa dilakukan mulai jam ' . 
                             $booking->start_time->format('H:i') . 
                             ' (tunggu ' . $waitMinutes . ' menit lagi)',
                'start_time' => $booking->start_time->format('H:i'),
                'wait_minutes' => $waitMinutes,
            ], 422);
        }

        // Check if table is currently available
        if ($booking->table->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Meja sedang tidak tersedia (status: ' . $booking->table->status . ')',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // AUTO-ADJUST DURATION for late check-in
            // If checking in late and there's a next booking, adjust duration to avoid conflict
            $adjustedDuration = $booking->duration_minutes;
            $adjustedEndTime = now()->addMinutes($booking->duration_minutes);
            $adjustmentMessage = '';
            
            // Check for next booking on this table
            $nextBooking = Booking::where('table_id', $booking->table_id)
                ->where('status', 'PENDING')
                ->where('start_time', '>', now())
                ->orderBy('start_time')
                ->first();
            
            if ($nextBooking) {
                // Calculate time until next booking
                $minutesUntilNext = now()->diffInMinutes($nextBooking->start_time);
                
                // If session would overlap with next booking, shorten it
                if ($adjustedDuration > $minutesUntilNext) {
                    $adjustedDuration = max(1, $minutesUntilNext); // At least 1 minute
                    $adjustedEndTime = now()->addMinutes($adjustedDuration);
                    
                    $originalDuration = $booking->duration_minutes;
                    $adjustmentMessage = " Durasi disesuaikan dari {$originalDuration} menit menjadi {$adjustedDuration} menit (ada booking berikutnya jam " . $nextBooking->start_time->format('H:i') . ")";
                }
            }
            
            // PENALTY BILLING: Charge original booking price (kesepakatan awal)
            // Even if duration adjusted, customer pays for what they booked
            $chargePrice = $booking->estimated_price; // Original booking price
            
            // Create session from booking with adjusted duration but original price
            $session = SessionBilliard::create([
                'table_id' => $booking->table_id,
                'customer_name' => $booking->customer_name,
                'rate_id' => $booking->rate_id,
                'start_time' => now(),
                'end_time' => $adjustedEndTime,
                'duration_minutes' => $adjustedDuration,
                'total_price' => $chargePrice, // Original booking price (penalty billing)
                'status' => 'playing',
                'auto_stop' => true,
                'is_open_billing' => false, // Booking always closed billing
            ]);

            // Update table status
            $booking->table->update(['status' => 'playing']);

            // Update booking status and link to session
            $booking->update([
                'status' => 'PLAYING',
                'session_id' => $session->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil. Sesi dimulai!' . $adjustmentMessage,
                'data' => [
                    'booking' => $booking->fresh(['table', 'rate', 'session']),
                    'session' => $session->load(['table', 'rate']),
                    'adjusted' => !empty($adjustmentMessage),
                    'original_duration' => $booking->duration_minutes,
                    'actual_duration' => $adjustedDuration,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal check-in: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'table_id' => 'required|exists:tables_billiard,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:30',
        ]);

        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = $startTime->copy()->addMinutes((int) $request->duration_minutes);

        $isAvailable = Booking::isTableAvailable($request->table_id, $startTime, $endTime);

        // If not available, find conflicting bookings
        $conflicts = [];
        if (!$isAvailable) {
            $conflicts = Booking::where('table_id', $request->table_id)
                ->whereIn('status', ['PENDING', 'CONFIRMED', 'CHECKED_IN'])
                ->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                })
                ->get(['id', 'customer_name', 'start_time', 'end_time']);
        }

        return response()->json([
            'success' => true,
            'available' => $isAvailable,
            'conflicts' => $conflicts,
        ]);
    }
}
