@extends('admin.layout', ['title' => 'Dashboard'])

@section('content')
    <h1 class="text-2xl font-black">Dashboard</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            'Total products' => $totalProducts,
            'Active products' => $activeProducts,
            'Total categories' => $totalCategories,
            'Active categories' => $activeCategories,
            'Hero banners' => $heroBanners,
        ] as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-black">Recent Products</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-[680px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-3 py-2">Name</th><th class="px-3 py-2">Category</th><th class="px-3 py-2">Price</th><th class="px-3 py-2">Active</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentProducts as $product)
                        <tr>
                            <td class="px-3 py-2 font-semibold">{{ $product->name }}</td>
                            <td class="px-3 py-2">{{ $product->category?->name }}</td>
                            <td class="px-3 py-2">{{ $product->price }}</td>
                            <td class="px-3 py-2">{{ $product->active ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
