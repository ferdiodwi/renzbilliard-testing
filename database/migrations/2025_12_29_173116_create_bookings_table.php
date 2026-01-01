<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables_billiard')->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->date('booking_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes');
            $table->foreignId('rate_id')->constrained('rates')->onDelete('restrict');
            $table->decimal('estimated_price', 10, 2);
            $table->enum('status', ['PENDING', 'PLAYING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->foreignId('session_id')->nullable()->constrained('sessions_billiard')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['table_id', 'booking_date', 'status']);
            $table->index(['start_time', 'end_time']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
