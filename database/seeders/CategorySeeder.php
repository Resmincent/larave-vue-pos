<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Makanan', 'slug' => 'makanan'],
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Peralatan Dapur', 'slug' => 'peralatan-dapur', 'parent_id' => 3],
            ['name' => 'Pakaian', 'slug' => 'pakaian'],
            ['name' => 'Aksesoris', 'slug' => 'aksesoris', 'parent_id' => 5],
            ['name' => 'Obat & Kesehatan', 'slug' => 'obat-kesehatan'],
            ['name' => 'Kosmetik', 'slug' => 'kosmetik'],
            ['name' => 'Buku', 'slug' => 'buku'],
            ['name' => 'Alat Tulis', 'slug' => 'alat-tulis', 'parent_id' => 9],
            ['name' => 'Snack', 'slug' => 'snack', 'parent_id' => 2],
            ['name' => 'Susu', 'slug' => 'susu', 'parent_id' => 1],
            ['name' => 'Handphone', 'slug' => 'handphone', 'parent_id' => 3],
            ['name' => 'Laptop', 'slug' => 'laptop', 'parent_id' => 3],
            ['name' => 'Sepatu', 'slug' => 'sepatu', 'parent_id' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
