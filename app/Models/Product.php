<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use SoftDeletes;

    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'tax_id',
        'sell_price',
        'cost_price',
        'unit',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function stocks()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sale_items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
