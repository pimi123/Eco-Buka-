<?php

namespace App\Console\Commands;

use App\Jobs\SendMetaEvent;
use App\Models\MetaConversionEvent;
use Illuminate\Console\Command;

class DispatchMetaEvents extends Command
{
    protected $signature = 'meta:dispatch {--limit=100}';
    protected $description = 'Recover due conversion outbox rows and enqueue delivery';
    public function handle(): int
    {
        if (! config('meta.enabled')) { $this->info('Meta delivery is disabled.'); return self::SUCCESS; }
        MetaConversionEvent::whereIn('status', ['pending', 'retry'])->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
            ->orderBy('id')->limit(max(1, min(1000, (int) $this->option('limit'))))->pluck('id')
            ->each(fn ($id) => SendMetaEvent::dispatch($id)->onQueue('meta'));
        return self::SUCCESS;
    }
}
