<?php

namespace App\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public static function generateCode(?Carbon $now = null)
    {
        $now ??= Carbon::now();
        $prefix = 'PUR-' . $now->format('Y') . '-';

        $lastCode = static::where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $sequence = 1;
        if ($lastCode) {
            $lastSequence = (int)substr($lastCode, strlen($prefix));
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
