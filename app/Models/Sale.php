<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Sale extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'code',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'paid_total',
        'change_due',
        'sold_at',
        'user_id',
        'note',
    ];


    // App\Models\Sale.php
    protected $casts = [
        'subtotal'        => 'int',
        'discount_total'  => 'int',
        'tax_total'       => 'int',
        'grand_total'     => 'int',
        'paid_total'      => 'int',
        'change_due'      => 'int',
        'sold_at' => 'datetime'
    ];


    public const STATUS_OPEN = 'OPEN';
    public const STATUS_PAID = 'PAID';
    public const STATUS_VOID = 'VOID';


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'sale_id', 'id');
    }


    public static function generateCode(?Carbon $now = null): string
    {
        $now ??= Carbon::now();
        $year   = $now->format('Y');
        $prefix = "S-{$year}-";

        // cari kode terakhir tahun ini
        $lastCode = static::where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $seq = 1;
        if ($lastCode) {
            $lastSeq = (int) substr($lastCode, -4);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
