<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{

    protected $fillable = [
        'purchase_id',
        'product_id',
        'qty',
        'cost_price',
        'discount',
        'tax_id',
        'line_total',
    ];


    protected $casts = [
        'qty' => 'integer',
        'cost_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function taxes()
    {
        return $this->belongsTo(Tax::class, 'tax_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
