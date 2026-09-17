<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\NestPay\PaymentResult;
use App\Services\Payments\NestPay\ProcessNestPayResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NestPayResultController extends Controller
{
    public function success(Request $request, ProcessNestPayResultService $payments): RedirectResponse
    {
        $result = $payments->process($request->all(), 'success');

        return redirect()->away($this->frontendUrl($result->approved ? '/order-success' : '/payment-failed', $result));
    }

    public function failure(Request $request, ProcessNestPayResultService $payments): RedirectResponse
    {
        $result = $payments->process($request->all(), 'failure');

        return redirect()->away($this->frontendUrl('/payment-failed', $result));
    }

    private function frontendUrl(string $path, PaymentResult $result): string
    {
        $query = http_build_query(array_filter([
            'payment' => $result->code,
            'order' => $result->payment?->order?->order_number,
            'token' => $result->payment?->order?->tracking_token,
        ]));

        return rtrim((string) config('nestpay.frontend_url'), '/').$path.($query ? '?'.$query : '');
    }
}
