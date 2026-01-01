<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionBilliard extends Model
{
    protected $table = 'sessions_billiard';

    protected $fillable = [
        'table_id',
        'customer_name',
        'rate_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_price',
        'status',
        'auto_stop',
        'is_open_billing',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'total_price' => 'decimal:2',
        'auto_stop' => 'boolean',
        'is_open_billing' => 'boolean',
    ];

    /**
     * Get the table for this session.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(TableBilliard::class, 'table_id');
    }

    /**
     * Get the rate for this session.
     */
    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class, 'rate_id');
    }

    /**
     * Get the transaction item for this session.
     */
    public function transactionItem(): HasOne
    {
        return $this->hasOne(TransactionItem::class, 'session_id');
    }

    /**
     * Get orders for this session.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    /**
     * Check if session is expired.
     */
    public function isExpired(): bool
    {
        // Open billing never expires
        if ($this->is_open_billing) {
            return false;
        }
        
        if (!$this->end_time) {
            return false;
        }
        
        return $this->status === 'playing' && Carbon::now()->gte($this->end_time);
    }

    /**
     * Get remaining time in seconds.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if ($this->status !== 'playing') {
            return 0;
        }

        // Open billing has unlimited time
        if ($this->is_open_billing) {
            return -1; // -1 indicates unlimited
        }

        if (!$this->end_time) {
            return 0;
        }

        $remaining = Carbon::now()->diffInSeconds($this->end_time, false);
        return max(0, $remaining);
    }

    /**
     * Get elapsed time in minutes.
     */
    public function getElapsedMinutesAttribute(): int
    {
        $elapsed = Carbon::now()->diffInMinutes($this->start_time);
        
        // For open billing, return actual elapsed time (no cap)
        if ($this->is_open_billing) {
            return $elapsed;
        }
        
        // For closed billing, cap at duration_minutes
        return min($elapsed, $this->duration_minutes ?? $elapsed);
    }

    /**
     * Get elapsed time in seconds.
     */
    public function getElapsedSecondsAttribute(): int
    {
        return Carbon::now()->diffInSeconds($this->start_time);
    }

    /**
     * Scope to get active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'playing');
    }

    /**
     * Scope to get expired sessions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'playing')
            ->where('end_time', '<=', Carbon::now());
    }

    /**
     * Scope to get sessions that need auto-stop.
     */
    public function scopeNeedsAutoStop($query)
    {
        return $query->expired()
            ->where('auto_stop', true)
            ->where('is_open_billing', false); // Skip open billing
    }

    /**
     * Get current price estimate for open billing.
     */
    public function getCurrentPriceEstimate(): float
    {
        if (!$this->is_open_billing) {
            return (float) $this->total_price;
        }
        
        $elapsedMinutes = $this->elapsed_minutes;
        
        // Minimum 2 hours (120 minutes) charge for open billing
        $chargeableMinutes = max(120, $elapsedMinutes);
        
        return $this->rate->calculatePrice($chargeableMinutes);
    }
}
