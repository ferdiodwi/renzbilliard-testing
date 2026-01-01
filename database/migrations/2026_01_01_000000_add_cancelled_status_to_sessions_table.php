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
        // Modify the status enum to include 'cancelled'
        DB::statement("ALTER TABLE sessions_billiard MODIFY COLUMN status ENUM('playing', 'finished', 'cancelled') DEFAULT 'playing'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE sessions_billiard MODIFY COLUMN status ENUM('playing', 'finished') DEFAULT 'playing'");
    }
};
