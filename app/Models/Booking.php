<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'table_id',
        'customer_name',
        'customer_phone',
        'booking_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'rate_id',
        'estimated_price',
        'status',
        'notes',
        'session_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'estimated_price' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(TableBilliard::class, 'table_id');
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionBilliard::class, 'session_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                     ->where('status', 'PENDING')
                     ->orderBy('start_time');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('booking_date', today())
                     ->orderBy('start_time');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isCheckInAllowed(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if this booking overlaps with given time range
     */
    public function isOverlapping($startTime, $endTime): bool
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return $this->start_time->lt($end) && $this->end_time->gt($start);
    }

    public static function getNextBookingForTable($tableId, $afterTime = null)
    {
        $afterTime = $afterTime ?? now();
        
        return static::where('table_id', $tableId)
            ->whereDate('booking_date', Carbon::parse($afterTime)->toDateString())
            ->where('status', 'PENDING')
            ->where('start_time', '>', $afterTime)
            ->orderBy('start_time')
            ->first();
    }

    public static function isTableAvailable($tableId, $startTime, $endTime, $excludeBookingId = null)
    {
        $query = static::where('table_id', $tableId)
            ->whereIn('status', ['PENDING', 'PLAYING']); // Active bookings: not yet started, or currently playing

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->where(function($q) use ($startTime, $endTime) {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            
            $q->where(function($query) use ($start, $end) {
                // Check if existing booking overlaps with new booking
                $query->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
            });
        })->exists();
    }
}
