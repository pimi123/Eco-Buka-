<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContactMessage extends Model
{
    public const PURPOSE_OFFER = 'offer';
    public const PURPOSE_RETURN = 'return';
    public const PURPOSE_SUPPORT = 'support';
    public const PURPOSE_GENERAL = 'general';

    public const PURPOSES = [
        self::PURPOSE_OFFER,
        self::PURPOSE_RETURN,
        self::PURPOSE_SUPPORT,
        self::PURPOSE_GENERAL,
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_READ,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'purpose',
        'status',
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'source_path',
        'ip_address',
        'user_agent',
        'admin_note',
        'read_at',
        'closed_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function setStatus(string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid contact message status.');
        }

        $this->status = $status;

        if ($status === self::STATUS_READ && ! $this->read_at) {
            $this->read_at = Carbon::now();
        }

        if ($status === self::STATUS_CLOSED && ! $this->closed_at) {
            $this->closed_at = Carbon::now();
        }
    }
}
