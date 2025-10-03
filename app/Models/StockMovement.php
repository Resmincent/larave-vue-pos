<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;


    protected $fillable = [
        'product_id',
        'qty_change',
        'type',
        'source_type',
        'source_id',
        'note',
    ];

    protected $casts = ['created_at' => 'datetime'];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
