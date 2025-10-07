<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{

    public function run()
    {
        $rows = [
            ['product_id' => 1,         'qty' => 1500],
            ['product_id' => 2,         'qty' => 1550],
            ['product_id' => 3,         'qty' => 2500],
            ['product_id' => 4,         'qty' => 3500],
            ['product_id' => 5,         'qty' => 500],
            ['product_id' => 6,         'qty' => 700],
            ['product_id' => 7,         'qty' => 1100],
            ['product_id' => 8,         'qty' => 1200],
            ['product_id' => 9,         'qty' => 1256],
            ['product_id' => 10,        'qty' => 1511],
            ['product_id' => 11,        'qty' => 1532],
            ['product_id' => 12,        'qty' => 4532],
            ['product_id' => 13,        'qty' => 3532],
            ['product_id' => 14,        'qty' => 5532],
            ['product_id' => 15,        'qty' => 532],
            ['product_id' => 16,        'qty' => 2532],
            ['product_id' => 17,        'qty' => 1522],
            ['product_id' => 18,        'qty' => 1562],
            ['product_id' => 19,        'qty' => 1732],
            ['product_id' => 20,        'qty' => 1832],
            ['product_id' => 21,        'qty' => 1332],
            ['product_id' => 22,        'qty' => 1032],
            ['product_id' => 23,        'qty' => 1732],
            ['product_id' => 24,        'qty' => 332],
            ['product_id' => 25,        'qty' => 232],
            ['product_id' => 26,        'qty' => 932],
            ['product_id' => 27,        'qty' => 1432],
            ['product_id' => 28,        'qty' => 1032],
            ['product_id' => 29,        'qty' => 1632],
            ['product_id' => 30,        'qty' => 1632],
            ['product_id' => 31,        'qty' => 6632],
            ['product_id' => 32,        'qty' => 4532],
            ['product_id' => 33,        'qty' => 3432],
            ['product_id' => 34,        'qty' => 2332],
            ['product_id' => 35,        'qty' => 5232],
            ['product_id' => 36,        'qty' => 1432],
            ['product_id' => 37,        'qty' => 1532],
            ['product_id' => 38,        'qty' => 1532],
            ['product_id' => 39,        'qty' => 1532],
            ['product_id' => 40,        'qty' => 1532],
            ['product_id' => 41,        'qty' => 1532],
            ['product_id' => 42,        'qty' => 1532],
            ['product_id' => 43,        'qty' => 1532],
            ['product_id' => 44,        'qty' => 1532],
            ['product_id' => 45,        'qty' => 1532],
            ['product_id' => 46,        'qty' => 1532],
            ['product_id' => 47,        'qty' => 1532],
            ['product_id' => 48,        'qty' => 1532],
            ['product_id' => 49,        'qty' => 1532],
            ['product_id' => 50,        'qty' => 1532],
        ];

        foreach ($rows as $inv) {
            Inventory::updateOrCreate(
                ['product_id' => $inv['product_id']],
                ['qty' => $inv['qty']]
            );
        }
    }
}
