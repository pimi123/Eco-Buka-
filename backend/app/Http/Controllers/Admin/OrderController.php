<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->withCount('items')
            ->when($request->string('search')->trim()->toString(), function ($builder, string $search): void {
                $builder->where(function ($inner) use ($search): void {
                    $inner->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($request->string('status')->trim()->toString(), fn ($builder, string $status) => $builder->where('status', $status))
            ->latest();

        $orders = $query->paginate(20)->withQueryString();
        $statuses = Order::STATUSES;

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('items.product');
        $statuses = Order::STATUSES;

        return view('admin.orders.show', compact('order', 'statuses'));
    }

public function update(Request $request, Order $order)
{
    $data = $request->validate([
        'status' => ['required', Rule::in(Order::STATUSES)],
        'admin_note' => ['nullable', 'string', 'max:2000'],
    ]);

    $oldStatus = $order->status;

    $order->setStatus($data['status']);
    $order->admin_note = $data['admin_note'] ?? null;
    $order->save();

    if (
        $oldStatus !== $order->status &&
        !empty($order->customer_email)
    ) {
        try {
            Mail::to($order->customer_email)
                ->send(new OrderStatusUpdated($order));
        } catch (\Throwable $e) {
            Log::error('Order status email could not be sent.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_email' => $order->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    return back()->with('status', 'Order updated.');
}
}
