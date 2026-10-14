<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NestPayCheckoutSession extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_ERROR = 'error';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'provider',
        'provider_order_id',
        'idempotency_key',
        'amount',
        'currency',
        'currency_code',
        'status',
        'order_payload',
        'items_snapshot',
        'request_metadata',
        'response_metadata',
        'response',
        'proc_return_code',
        'auth_code',
        'host_ref_num',
        'trans_id',
        'md_status',
        'masked_pan',
        'payment_method',
        'installment_count',
        'error_message',
        'paid_at',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_payload' => 'array',
        'items_snapshot' => 'array',
        'request_metadata' => 'array',
        'response_metadata' => 'array',
        'installment_count' => 'integer',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
