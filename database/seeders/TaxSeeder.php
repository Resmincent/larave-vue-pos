<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            [
                'name' => 'PPN 10%',
                'rate' => 10,
            ],
            [
                'name' => 'PPN 5%',
                'rate' => 5,
            ],
            [
                'name' => 'Tanpa Pajak',
                'rate' => 0,
            ],
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate(
                ['name' => $tax['name']], // biar tidak dobel
                ['rate' => $tax['rate']]
            );
        }
    }
}
