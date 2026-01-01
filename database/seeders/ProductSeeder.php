<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $makananCatId = Category::where('name', 'Makanan')->value('id');
        $minumanCatId = Category::where('name', 'Minuman')->value('id');
        $snackCatId = Category::where('name', 'Snack')->value('id');

        $products = [
            // Minuman
            ['name' => 'Air Mineral', 'category_id' => $minumanCatId, 'price' => 5000, 'stock' => 100, 'is_available' => true],
            ['name' => 'Teh Botol', 'category_id' => $minumanCatId, 'price' => 7000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Coca Cola', 'category_id' => $minumanCatId, 'price' => 10000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Sprite', 'category_id' => $minumanCatId, 'price' => 10000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Fanta', 'category_id' => $minumanCatId, 'price' => 10000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Kopi Hitam', 'category_id' => $minumanCatId, 'price' => 8000, 'stock' => 30, 'is_available' => true],
            ['name' => 'Kopi Susu', 'category_id' => $minumanCatId, 'price' => 10000, 'stock' => 30, 'is_available' => true],
            ['name' => 'Cappuccino', 'category_id' => $minumanCatId, 'price' => 12000, 'stock' => 30, 'is_available' => true],
            ['name' => 'Es Teh Manis', 'category_id' => $minumanCatId, 'price' => 6000, 'stock' => 50, 'is_available' => true],
            ['name' => 'Es Jeruk', 'category_id' => $minumanCatId, 'price' => 8000, 'stock' => 40, 'is_available' => true],
            ['name' => 'Jus Alpukat', 'category_id' => $minumanCatId, 'price' => 15000, 'stock' => 20, 'is_available' => true],
            ['name' => 'Jus Mangga', 'category_id' => $minumanCatId, 'price' => 15000, 'stock' => 20, 'is_available' => true],

            // Makanan Ringan / Snack
            ['name' => 'Keripik Kentang', 'category_id' => $snackCatId, 'price' => 12000, 'stock' => 40, 'is_available' => true],
            ['name' => 'Kacang Atom', 'category_id' => $snackCatId, 'price' => 10000, 'stock' => 40, 'is_available' => true],
            ['name' => 'Popcorn', 'category_id' => $snackCatId, 'price' => 8000, 'stock' => 30, 'is_available' => true],
            ['name' => 'Nachos', 'category_id' => $snackCatId, 'price' => 15000, 'stock' => 25, 'is_available' => true],
            ['name' => 'French Fries', 'category_id' => $snackCatId, 'price' => 18000, 'stock' => 30, 'is_available' => true],
            ['name' => 'Onion Rings', 'category_id' => $snackCatId, 'price' => 20000, 'stock' => 25, 'is_available' => true],
            ['name' => 'Es Krim Vanila', 'category_id' => $snackCatId, 'price' => 12000, 'stock' => 25, 'is_available' => true],
            ['name' => 'Es Krim Cokelat', 'category_id' => $snackCatId, 'price' => 12000, 'stock' => 25, 'is_available' => true],
            ['name' => 'Brownies', 'category_id' => $snackCatId, 'price' => 15000, 'stock' => 20, 'is_available' => true],

            // Makanan Berat
            ['name' => 'Nasi Goreng', 'category_id' => $makananCatId, 'price' => 25000, 'stock' => 20, 'is_available' => true],
            ['name' => 'Mie Goreng', 'category_id' => $makananCatId, 'price' => 22000, 'stock' => 20, 'is_available' => true],
            ['name' => 'Nasi Ayam Geprek', 'category_id' => $makananCatId, 'price' => 28000, 'stock' => 15, 'is_available' => true],
            ['name' => 'Nasi Ayam Penyet', 'category_id' => $makananCatId, 'price' => 28000, 'stock' => 15, 'is_available' => true],
            ['name' => 'Burger Beef', 'category_id' => $makananCatId, 'price' => 30000, 'stock' => 15, 'is_available' => true],
            ['name' => 'Burger Chicken', 'category_id' => $makananCatId, 'price' => 28000, 'stock' => 15, 'is_available' => true],
            ['name' => 'Sandwich Club', 'category_id' => $makananCatId, 'price' => 25000, 'stock' => 15, 'is_available' => true],
            ['name' => 'Pizza Slice', 'category_id' => $makananCatId, 'price' => 20000, 'stock' => 20, 'is_available' => true],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
