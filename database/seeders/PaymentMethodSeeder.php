<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            ['name' => 'Cash',             'code' => 'CASH',            'is_active' => true],
            ['name' => 'Bank Transfer',    'code' => 'TRANSFER',        'is_active' => true],
            ['name' => 'Debit Card',       'code' => 'DEBIT',           'is_active' => true],
            ['name' => 'Credit Card',      'code' => 'CREDIT',          'is_active' => true],
            ['name' => 'QRIS',             'code' => 'QRIS',            'is_active' => true],

            ['name' => 'GoPay',            'code' => 'EWALLET_GOPAY',   'is_active' => true],
            ['name' => 'OVO',              'code' => 'EWALLET_OVO',     'is_active' => true],
            ['name' => 'DANA',             'code' => 'EWALLET_DANA',    'is_active' => true],

            ['name' => 'VA BCA',           'code' => 'VA_BCA',          'is_active' => true],
            ['name' => 'VA BRI',           'code' => 'VA_BRI',          'is_active' => true],
            ['name' => 'VA BNI',           'code' => 'VA_BNI',          'is_active' => true],
            ['name' => 'VA Mandiri',       'code' => 'VA_MANDIRI',      'is_active' => true],
        ];

        // Tambah timestamps agar tidak null (kalau tabel punya timestamps)
        $rows = array_map(function ($r) use ($now) {
            return $r + ['created_at' => $now, 'updated_at' => $now];
        }, $rows);

        PaymentMethod::query()->upsert(
            $rows,
            uniqueBy: ['code'],
            update: ['name', 'is_active', 'updated_at']
        );
    }
}
