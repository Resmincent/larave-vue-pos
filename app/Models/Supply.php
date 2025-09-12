<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};


class Supply extends Model {


    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchases() {
        return $this->hasMany(Purchase::class, 'supplier_id' . 'id');
    }
}
