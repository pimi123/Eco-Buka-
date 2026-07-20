@extends('admin.layout', ['title' => 'Orders'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-black">Orders</h1>
    </div>

    <form class="mt-5 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]" method="get">
        <input class="rounded-md border border-slate-300 px-3 py-2" type="search" name="search" value="{{ request('search') }}" placeholder="Search order, customer, phone">
        <select class="rounded-md border border-slate-300 px-3 py-2" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">City</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-black">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3">{{ $order->customer_phone }}</td>
                            <td class="px-4 py-3">{{ $order->city }}</td>
                            <td class="px-4 py-3">{{ $order->items_count }}</td>
                            <td class="px-4 py-3 font-semibold">€{{ number_format((float) $order->total, 2) }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ str($order->status)->headline() }}</span></td>
                            <td class="px-4 py-3">{{ $order->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3"><a class="rounded-md border border-slate-300 px-3 py-2 font-semibold" href="{{ route('admin.orders.show', $order) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-slate-500" colspan="9">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
