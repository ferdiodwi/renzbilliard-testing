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
        Schema::table('products', function (Blueprint $table) {
            // Add category_id column (nullable first for data migration)
            $table->foreignId('category_id')->nullable()->after('name')->constrained()->onDelete('restrict');
        });

        // Migrate existing enum data to category_id
        // Map: makanan -> 1, minuman -> 2, snack -> 3
        DB::statement("UPDATE products SET category_id = 1 WHERE category = 'makanan'");
        DB::statement("UPDATE products SET category_id = 2 WHERE category = 'minuman'");
        DB::statement("UPDATE products SET category_id = 3 WHERE category = 'snack'");

        // Drop the old enum column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        // Make category_id NOT NULL now that data is migrated
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add back enum column
            $table->enum('category', ['makanan', 'minuman', 'snack'])->after('name');
        });

        // Migrate data back
        DB::statement("UPDATE products SET category = 'makanan' WHERE category_id = 1");
        DB::statement("UPDATE products SET category = 'minuman' WHERE category_id = 2");
        DB::statement("UPDATE products SET category = 'snack' WHERE category_id = 3");

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
