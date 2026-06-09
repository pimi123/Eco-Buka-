<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Eco Buka Admin' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-950">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="border-b border-slate-200 bg-white p-4 lg:border-b-0 lg:border-r">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-black">Eco Buka CMS</a>
            @auth
                <nav class="mt-6 flex gap-2 overflow-x-auto lg:grid">
                    @foreach ([
                        'categories' => 'Categories',
                        'products' => 'Products',
                        'hero-banners' => 'Hero Banners',
                        'promo-cards' => 'Promo Cards',
                        'showcase-sections' => 'Showcase Sections',
                        'navigation-cards' => 'Navigation Cards',
                        'feature-banners' => 'Feature Banners',
                    ] as $key => $label)
                        <a class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-950" href="{{ route('admin.content.index', $key) }}">{{ $label }}</a>
                    @endforeach
                </nav>
                <form class="mt-6" method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold">Logout</button>
                </form>
            @endauth
        </aside>
        <main class="min-w-0 p-4 sm:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
