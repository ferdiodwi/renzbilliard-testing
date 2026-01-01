<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Widen enum to include both old and new statuses temporarily
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('PENDING', 'CONFIRMED', 'CHECKED_IN', 'PLAYING', 'COMPLETED', 'CANCELLED', 'NO_SHOW') NOT NULL DEFAULT 'PENDING'");
        
        // Step 2: Update existing records to new status values
        DB::table('bookings')
            ->where('status', 'CHECKED_IN')
            ->update(['status' => 'PLAYING']);
        
        DB::table('bookings')
            ->where('status', 'CONFIRMED')
            ->update(['status' => 'PENDING']);
            
        DB::table('bookings')
            ->where('status', 'NO_SHOW')
            ->update(['status' => 'CANCELLED']);
        
        // Step 3: Narrow enum to only new statuses
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('PENDING', 'PLAYING', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('PENDING', 'CONFIRMED', 'CHECKED_IN', 'CANCELLED', 'COMPLETED', 'NO_SHOW') NOT NULL DEFAULT 'PENDING'");
        
        // Revert data
        DB::table('bookings')
            ->where('status', 'PLAYING')
            ->update(['status' => 'CHECKED_IN']);
    }
};
