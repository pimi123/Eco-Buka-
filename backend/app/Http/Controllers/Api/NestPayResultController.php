<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\NestPay\ProcessNestPayResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NestPayResultController extends Controller
{
    public function success(Request $request, ProcessNestPayResultService $payments): RedirectResponse
    {
        $result = $payments->process($request->all(), 'success');

        return redirect()->away($this->frontendUrl($result->approved ? '/order-success' : '/checkout', $result->code));
    }

    public function failure(Request $request, ProcessNestPayResultService $payments): RedirectResponse
    {
        $result = $payments->process($request->all(), 'failure');

        return redirect()->away($this->frontendUrl('/checkout', $result->code));
    }

    private function frontendUrl(string $path, string $paymentStatus): string
    {
        return rtrim((string) config('nestpay.frontend_url'), '/').$path.'?payment='.$paymentStatus;
    }
}
