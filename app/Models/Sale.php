<?php

namespace App\Models;

use App\Models\{Customer, SaleItem, User};
use Illuminate\Database\Eloquent\{Model, SoftDeletes};


class Sale extends Model {

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

    protected $casts = ['sold_at' => 'datetime'];

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_PAID = 'PAID';
    public const STATUS_VOID = 'VOID';


    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function saleItems() {
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }

    public function payments() {
        return $this->hasMany(Payment::class, 'sale_id', 'id');
    }
}
