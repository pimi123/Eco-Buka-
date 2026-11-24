<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\MetaConversionEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetaTrackingController extends Controller
{
    public function config()
    {
        return response()->json(['enabled' => (bool) config('meta.enabled'), 'pixel_id' => (string) config('meta.pixel_id'),
            'catalog_ids_verified' => (bool) config('meta.catalog_ids_verified')])->header('Cache-Control', 'no-store');
    }

    public function revoke(Request $request)
    {
        $data = $request->validate(['orders' => ['required', 'array', 'max:20'], 'orders.*.id' => ['required', 'integer'], 'orders.*.token' => ['required', 'string', 'max:255']]);
        DB::transaction(function () use ($data) {
            foreach ($data['orders'] as $receipt) {
                $order = Order::whereKey($receipt['id'])->lockForUpdate()->first();
                if (! $order || ! hash_equals((string) $order->tracking_token, $receipt['token'])) continue;
                app(\App\Services\Tracking\MetaConversions::class)->withdraw($order);
            }
        });
        return response()->json(['revoked' => true]);
    }
}
