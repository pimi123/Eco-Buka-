@extends('admin.layout', ['title' => 'Homepage Content Display'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-black">Homepage Content Display</h1>
            <p class="mt-2 max-w-4xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
                This page shows the homepage content blocks in the order they appear. Use each section action to edit its title, source, layout, cards, banners, and related visual content from the correct focused screen.
            </p>
        </div>
        <a class="rounded-md bg-slate-950 px-4 py-3 text-center text-sm font-bold text-white" href="{{ route('admin.content.create', 'showcase-sections') }}">Add Homepage Section</a>
    </div>

    <div class="mt-6 grid gap-4">
        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-slate-500">Fixed top area</p>
                    <h2 class="mt-1 text-xl font-black">01 - Hero / Main Banner</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Controlled by Hero Banners. Active banners: {{ $heroBannerCount }}.</p>
                </div>
                <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.content.index', 'hero-banners') }}">Manage Hero Banners</a>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-slate-500">Fixed navigation strip</p>
                    <h2 class="mt-1 text-xl font-black">02 - Category Carousel</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-600">{{ $categoryCarouselLabel }}. Controlled by active Categories and their sort order.</p>
                </div>
                <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.content.index', 'categories') }}">Manage Categories</a>
            </div>
        </section>
    </div>

    <form class="mt-6 grid gap-4" method="post" action="{{ route('admin.homepage-content.order') }}">
        @csrf

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-black">Configurable Homepage Sections</h2>
            <button class="rounded-md bg-slate-950 px-4 py-3 text-sm font-bold text-white" type="submit">Save section order</button>
        </div>

        @forelse ($sections as $index => $entry)
            @php
                $section = $entry['section'];
                $type = $section->section_type ?: 'product_grid';
                $sourceLabel = match ($section->source_type) {
                    'category' => 'Category',
                    'collection' => 'Collection / Campaign',
                    'manual_cards' => 'Manual Cards',
                    'manual_products' => 'Manual Products',
                    default => str($section->source_type ?: 'Not configured')->headline(),
                };
                $promoCardCount = $section->promo_cards_count + $entry['fallback_promo_cards_count'];
                $sourceProductsCount = $entry['source_products_count'];
                $contentCount = $type === 'promo_cards'
                    ? $promoCardCount
                    : $promoCardCount + $sourceProductsCount;
                $typeHelp = match ($type) {
                    'promo_cards' => 'This section displays marketing cards. Edit the section title, subtitle, order, and the cards from one screen.',
                    'featured_category' => 'This section displays a category or collection with a large banner and product cards.',
                    'mixed_showcase' => 'This section displays selected products plus optional navigation cards and a feature banner.',
                    'product_grid', 'product_carousel' => 'This section displays product cards from a category, collection, or manual selection.',
                    'video_banner' => 'This section is controlled by Feature Banners with video/image fields.',
                    default => 'This section controls one homepage display block.',
                };
            @endphp
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_220px] xl:items-start">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-black text-white">
                                {{ str_pad((string) ($index + 3), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-600">{{ str($section->section_type ?: 'section')->headline() }}</span>
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-black uppercase',
                                'bg-emerald-100 text-emerald-800' => $section->active,
                                'bg-red-100 text-red-800' => !$section->active,
                            ])>{{ $section->active ? 'Enabled' : 'Disabled' }}</span>
                        </div>

                        <h3 class="mt-3 text-xl font-black">{{ $section->title ?: $section->section_key }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">Internal key: {{ $section->section_key }}</p>
                        @if ($section->subtitle)
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">{{ $section->subtitle }}</p>
                        @endif
                        <p class="mt-2 max-w-3xl rounded-lg bg-amber-50 p-3 text-sm font-semibold leading-6 text-amber-900">{{ $typeHelp }}</p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs font-black uppercase text-slate-500">Content source</p>
                                <p class="mt-1 text-sm font-bold">{{ $sourceLabel }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $section->source_slug ?: ($section->source_id ? 'ID '.$section->source_id : 'No source selected') }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs font-black uppercase text-slate-500">Display limit</p>
                                <p class="mt-1 text-sm font-bold">{{ $section->display_limit ?: 'Default' }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs font-black uppercase text-slate-500">Assigned content</p>
                                <p class="mt-1 text-sm font-bold">{{ $contentCount }} items</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    @if ($type === 'promo_cards')
                                        {{ $promoCardCount }} promo cards
                                    @else
                                        {{ $sourceProductsCount }} source products, {{ $promoCardCount }} promo cards
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs font-black uppercase text-slate-500">Extra visuals</p>
                                <p class="mt-1 text-sm font-bold">{{ $entry['navigation_cards_count'] }} nav cards</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $entry['feature_banners_count'] }} feature banners</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <label class="grid gap-1">
                            <span class="text-xs font-black uppercase text-slate-500">Position</span>
                            <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="sections[{{ $section->id }}][sort_order]" value="{{ $section->sort_order }}" min="0">
                        </label>
                        <label class="flex items-center gap-2 text-sm font-bold">
                            <input type="hidden" name="sections[{{ $section->id }}][active]" value="0">
                            <input type="checkbox" name="sections[{{ $section->id }}][active]" value="1" @checked($section->active)>
                            Enabled
                        </label>
                        @if ($type === 'promo_cards')
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.homepage-content.promo-cards.edit', $section) }}">Edit Section and Cards</a>
                        @elseif (in_array($type, ['product_grid', 'product_carousel', 'featured_category'], true))
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.homepage-content.featured-products.edit', $section) }}">Edit Banner and Products</a>
                        @elseif ($type === 'mixed_showcase')
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.homepage-content.mixed-showcase.edit', $section) }}">Edit Complete Section</a>
                        @elseif ($type === 'video_banner')
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.content.edit', ['showcase-sections', $section]) }}">Edit Section Info</a>
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.content.index', 'feature-banners') }}">Manage Video Banner</a>
                        @else
                            <a class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-bold" href="{{ route('admin.content.edit', ['showcase-sections', $section]) }}">Edit Section Info</a>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm font-semibold text-slate-600">
                No homepage sections created yet.
            </div>
        @endforelse
    </form>
@endsection
