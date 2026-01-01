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
        Schema::table('tables_billiard', function (Blueprint $table) {
            $table->foreignId('rate_id')->nullable()->after('table_number')->constrained('rates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables_billiard', function (Blueprint $table) {
            $table->dropForeign(['rate_id']);
            $table->dropColumn('rate_id');
        });
    }
};
