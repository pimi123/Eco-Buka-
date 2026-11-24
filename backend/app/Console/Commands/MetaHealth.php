<?php

namespace App\Console\Commands;

use App\Models\MetaConversionEvent;
use Illuminate\Console\Command;

class MetaHealth extends Command
{
    protected $signature = 'meta:health';
    protected $description = 'Exit nonzero for missing delivery configuration or unhealthy conversion outbox';
    public function handle(): int
    {
        if (! config('meta.enabled')) { $this->info('Meta is disabled; no events will be transmitted.'); return self::SUCCESS; }
        if (! config('meta.access_token') || ! preg_match('/^\d+$/', (string) config('meta.pixel_id')) || ! preg_match('/^v\d+\.\d+$/', (string) config('meta.api_version'))) {
            $this->error('Meta delivery configuration is incomplete.'); return self::FAILURE;
        }
        $failed = MetaConversionEvent::whereIn('status', ['failed', 'expired'])->count();
        $overdue = MetaConversionEvent::whereIn('status', ['pending', 'retry'])->where('occurred_at', '<', now()->subMinutes(30))->count();
        $this->line('Failed/expired: '.$failed.'; pending/retry older than 30 minutes: '.$overdue.'.');
        return $failed || $overdue ? self::FAILURE : self::SUCCESS;
    }
}
