<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'code',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'received_at',
        'note',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_RECEIVED = 'RECEIVED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function supplier()
    {
        return $this->belongsTo(Supply::class, 'supplier_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }
}
