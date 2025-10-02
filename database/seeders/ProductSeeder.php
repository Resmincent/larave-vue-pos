<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // contoh dummy data produk
        $products = [
            [
                'sku' => 'PRD001',
                'name' => 'Pensil 2B',
                'category_id' => 1,
                'tax_id' => 1,
                'sell_price' => 3000,
                'cost_price' => 2000,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD002',
                'name' => 'Buku Tulis',
                'category_id' => 1,
                'tax_id' => 1,
                'sell_price' => 7000,
                'cost_price' => 5000,
                'unit' => 'pcs',
                'is_active' => true,
            ],
            [
                'sku' => 'PRD003',
                'name' => 'Penghapus',
                'category_id' => 1,
                'tax_id' => null,
                'sell_price' => 2500,
                'cost_price' => 1500,
                'unit' => 'pcs',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
