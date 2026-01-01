<?php

namespace Database\Seeders;

use App\Models\Rate;
use App\Models\TableBilliard;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,  // Must be seeded before products
            ProductSeeder::class,
        ]);

        // Create 6 rates with table-specific names
        $rateMeja1 = Rate::create([
            'name' => 'Meja 1',
            'price_per_hour' => 15000,
        ]);

        $rateMeja2 = Rate::create([
            'name' => 'Meja 2',
            'price_per_hour' => 15000,
        ]);

        $rateMeja3 = Rate::create([
            'name' => 'Meja 3',
            'price_per_hour' => 15000,
        ]);

        $rateMeja4 = Rate::create([
            'name' => 'Meja 4',
            'price_per_hour' => 20000,
        ]);

        $rateMeja5 = Rate::create([
            'name' => 'Meja 5',
            'price_per_hour' => 20000,
        ]);

        $rateMeja6 = Rate::create([
            'name' => 'Meja 6',
            'price_per_hour' => 20000,
        ]);

        // Create 6 tables
        for ($i = 1; $i <= 6; $i++) {
            TableBilliard::create([
                'table_number' => (string)$i,
                'status' => 'available',
            ]);
        }
    }
}
