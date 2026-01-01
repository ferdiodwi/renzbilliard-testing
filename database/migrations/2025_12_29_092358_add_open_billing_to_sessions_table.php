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
        Schema::table('sessions_billiard', function (Blueprint $table) {
            // Add open billing flag
            $table->boolean('is_open_billing')->default(false)->after('auto_stop');
            
            // Make duration_minutes nullable (NULL for open billing)
            $table->integer('duration_minutes')->nullable()->change();
            
            // Make end_time nullable (NULL for open billing - unlimited)
            $table->dateTime('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions_billiard', function (Blueprint $table) {
            $table->dropColumn('is_open_billing');
            
            // Revert to non-nullable
            $table->integer('duration_minutes')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();
        });
    }
};
