<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id', 'id');
    }
}
