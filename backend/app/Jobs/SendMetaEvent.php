<?php

namespace App\Jobs;

use App\Services\Tracking\DeliverMetaEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMetaEvent implements ShouldQueue
{
    use Queueable;
    public $tries = 3;
    public $timeout = 25;
    public function __construct(public int $eventId) {}
    public function handle(DeliverMetaEvent $delivery): void { $delivery->send($this->eventId); }
}
