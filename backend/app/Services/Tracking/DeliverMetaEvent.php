<?php

namespace App\Services\Tracking;

use App\Models\MetaConversionEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeliverMetaEvent
{
    public function send(int $id): void
    {
        if (! config('meta.enabled') || ! config('meta.access_token') || ! preg_match('/^v\d+\.\d+$/', (string) config('meta.api_version')) || ! preg_match('/^\d+$/', (string) config('meta.pixel_id'))) return;
        Cache::lock('meta-delivery-'.$id, 60)->get(function () use ($id): void {
            $event = MetaConversionEvent::find($id);
            if (! $event || ! in_array($event->status, ['pending', 'retry'], true) || $event->next_attempt_at?->isFuture()) return;
            if ($event->dataset_id !== (string) config('meta.pixel_id')) {
                $event->update(['status' => 'failed', 'last_error' => 'dataset_changed']);
                return;
            }
            if ($event->occurred_at->lt(now()->subDays(7)) || ($event->first_attempt_at && $event->first_attempt_at->lt(now()->subHours(47)))) {
                $event->update(['status' => 'expired', 'last_error' => 'delivery_window_expired']);
                return;
            }
            if ($event->order_id && \App\Models\Order::whereKey($event->order_id)->where('status', 'cancelled')->exists()) {
                $event->update(['status' => 'cancelled', 'payload' => null]);
                return;
            }
            if (! $event->payload) return;
            $consent = \App\Models\Order::find($event->order_id)?->tracking_context;
            if (($consent['marketing'] ?? false) !== true) {
                $event->update(['status' => 'revoked', 'payload' => null]);
                return;
            }
            $event->update(['attempts' => $event->attempts + 1, 'first_attempt_at' => $event->first_attempt_at ?: now()]);
            $body = ['data' => [$event->payload]];
            if (config('meta.test_event_code')) $body['test_event_code'] = config('meta.test_event_code');
            try {
                $response = Http::asJson()->withToken(config('meta.access_token'))->connectTimeout(5)->timeout(15)
                    ->post('https://graph.facebook.com/'.config('meta.api_version').'/'.$event->dataset_id.'/events', $body);
                if ($response->successful() && (int) $response->json('events_received', 0) === 1) {
                    $event->update(['status' => 'delivered', 'delivered_at' => now(), 'last_error' => null, 'next_attempt_at' => null]);
                    return;
                }
                $code = (int) $response->json('error.code', 0);
                $transient = $response->status() === 429 || $response->serverError() || $response->json('error.is_transient') === true || in_array($code, [1, 2, 4, 17, 32, 613], true);
                $this->failure($event, 'meta_http_'.$response->status().'_code_'.$code, $transient);
            } catch (Throwable) {
                // Never persist an exception message: HTTP errors can include tokens or customer payloads.
                $this->failure($event, 'transport_error', true);
            }
        });
    }

    private function failure(MetaConversionEvent $event, string $error, bool $retry): void
    {
        $retry = $retry && $event->attempts < config('meta.max_attempts');
        $event->update(['status' => $retry ? 'retry' : 'failed', 'last_error' => $error,
            'next_attempt_at' => $retry ? now()->addSeconds(min(3600, 30 * (2 ** min($event->attempts, 7))) + random_int(0, 15)) : null]);
    }
}
