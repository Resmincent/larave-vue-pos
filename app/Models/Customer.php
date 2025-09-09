<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $fillable = ['name', 'phone'];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id', 'id');
    }
}
