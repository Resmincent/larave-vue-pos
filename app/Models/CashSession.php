<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'status'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_CLOSED = 'CLOSED';


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
