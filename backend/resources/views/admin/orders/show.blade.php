@extends('admin.layout', ['title' => $order->order_number])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.orders.index') }}">← Back to orders</a>
            <h1 class="mt-2 text-2xl font-black">{{ $order->order_number }}</h1>
            <p class="mt-1 text-sm text-slate-500">Placed {{ $order->created_at?->format('d M Y H:i') }}</p>
        </div>
        <span class="w-fit rounded-full bg-slate-950 px-4 py-2 text-sm font-bold text-white">{{ str($order->status)->headline() }}</span>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_360px]">
        <section class="grid gap-5">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Items</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr><th class="py-2">Product</th><th class="py-2">Qty</th><th class="py-2">Unit</th><th class="py-2 text-right">Line total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-3 font-semibold">{{ $item->product_name }}</td>
                                    <td class="py-3">{{ $item->quantity }}</td>
                                    <td class="py-3">€{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="py-3 text-right font-semibold">€{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 grid justify-end gap-1 text-sm">
                    <p>Subtotal: <strong>€{{ number_format((float) $order->subtotal, 2) }}</strong></p>
                    <p>Delivery: <strong>€{{ number_format((float) $order->delivery_fee, 2) }}</strong></p>
                    <p class="text-lg">Total: <strong>€{{ number_format((float) $order->total, 2) }}</strong></p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Customer and delivery</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-slate-500">Name</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Phone</dt><dd>{{ $order->customer_phone }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Email</dt><dd>{{ $order->customer_email ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Country</dt><dd>{{ $order->country ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Municipality / City</dt><dd>{{ $order->municipality ?: $order->city }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Postal code</dt><dd>{{ $order->postal_code ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Policy accepted</dt><dd>{{ $order->policy_accepted_at?->format('d M Y H:i') ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">Address</dt><dd>{{ $order->delivery_address }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">Delivery details</dt><dd>{{ $order->delivery_details ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">Customer note</dt><dd>{{ $order->customer_note ?: '-' }}</dd></div>
                </dl>
            </div>
        </section>

        <form class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm" method="post" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('patch')
            <h2 class="text-lg font-black">Manage order</h2>
            <label class="mt-4 grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Status</span>
                <select class="rounded-md border border-slate-300 px-3 py-2" name="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-4 grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Admin note</span>
                <textarea class="min-h-36 rounded-md border border-slate-300 px-3 py-2" name="admin_note">{{ old('admin_note', $order->admin_note) }}</textarea>
            </label>
            <button class="mt-4 w-full rounded-md bg-slate-950 px-4 py-3 text-sm font-bold text-white" onclick="return this.form.status.value !== 'cancelled' || confirm('Cancel this order?')">Save order</button>

            <div class="mt-5 grid gap-2 text-xs text-slate-500">
                <p>Confirmed: {{ $order->confirmed_at?->format('d M Y H:i') ?: '-' }}</p>
                <p>Processing: {{ $order->processing_at?->format('d M Y H:i') ?: '-' }}</p>
                <p>Completed: {{ $order->completed_at?->format('d M Y H:i') ?: '-' }}</p>
                <p>Cancelled: {{ $order->cancelled_at?->format('d M Y H:i') ?: '-' }}</p>
            </div>
        </form>
    </div>
@endsection
