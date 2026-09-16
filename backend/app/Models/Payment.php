<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const PROVIDER_NESTPAY = 'nestpay';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_ERROR = 'error';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_DECLINED,
        self::STATUS_ERROR,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_id',
        'provider',
        'provider_order_id',
        'amount',
        'currency',
        'currency_code',
        'status',
        'response',
        'proc_return_code',
        'auth_code',
        'host_ref_num',
        'trans_id',
        'md_status',
        'masked_pan',
        'payment_method',
        'error_message',
        'idempotency_key',
        'request_metadata',
        'response_metadata',
        'paid_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'request_metadata' => 'array',
        'response_metadata' => 'array',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
