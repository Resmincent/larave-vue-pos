<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rate'];

    public function products()
    {
        return $this->hasMany(Product::class, 'tax_id', 'id');
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'tax_id', 'id');
    }
}
