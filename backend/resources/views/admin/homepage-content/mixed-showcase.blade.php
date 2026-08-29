@extends('admin.layout', ['title' => 'Edit Mixed Showcase Section'])

@php
    $selectedCategory = $section->source_type === 'category'
        ? $categories->first(fn ($category) => (string) $category->id === (string) $section->source_id || $category->slug === $section->source_slug)
        : null;
    $selectedCollection = $section->source_type === 'collection'
        ? $collections->first(fn ($collection) => (string) $collection->id === (string) $section->source_id || $collection->slug === $section->source_slug)
        : null;
    $selectedCategoryId = old('category_id', $selectedCategory?->id);
    $selectedCollectionId = old('collection_id', $selectedCollection?->id);
    $sourceType = old('source_type', $section->source_type ?: 'manual_products');
    $cardRows = $navigationCards->values();
    $blankCardCount = max(2, 4 - $cardRows->count());
@endphp

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.homepage-content.index') }}">Back to Homepage Content Display</a>
            <h1 class="mt-2 text-2xl font-black">Edit Mixed Showcase</h1>
            <p class="mt-2 max-w-4xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
                Manage this homepage block from one screen: the section title, product source, optional manually selected products, right-side redirect cards, and the large feature banner below it.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
            <p class="font-black">Please fix these fields:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form class="mt-5 grid gap-5" method="post" enctype="multipart/form-data" action="{{ route('admin.homepage-content.mixed-showcase.update', $section) }}">
        @csrf
        @method('put')

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Section Info</p>
                <h2 class="mt-1 text-xl font-black">{{ $section->title ?: str($section->section_key)->headline() }}</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Internal key: {{ $section->section_key }}</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Title</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="title" value="{{ old('title', $section->title) }}" placeholder="STREAM Solar Plant">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Homepage Position</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" min="0">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Subtitle</span>
                    <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="subtitle" placeholder="Short text above the product cards.">{{ old('subtitle', $section->subtitle) }}</textarea>
                </label>

                <label class="flex items-center gap-2 text-sm font-bold">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $section->active))>
                    Enabled on homepage
                </label>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Product Source</p>
                <h2 class="mt-1 text-xl font-black">Products shown in the grid</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Choose a dynamic category/collection, or use manual products when the homepage needs a curated order.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Source Type</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="source_type" data-source-type>
                        <option value="manual_products" @selected($sourceType === 'manual_products')>Manual product selection</option>
                        <option value="category" @selected($sourceType === 'category')>Category / product-based group</option>
                        <option value="collection" @selected($sourceType === 'collection')>Collection / solution or campaign</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Display Limit</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="display_limit" value="{{ old('display_limit', $section->display_limit ?: 4) }}" min="1" max="24">
                </label>

                <label class="grid gap-2" data-category-source>
                    <span class="text-xs font-bold uppercase text-slate-500">Category</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="category_id">
                        <option value="">Choose category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }} ({{ $category->slug }})</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Best for product type or series pages.</span>
                </label>

                <label class="grid gap-2" data-collection-source>
                    <span class="text-xs font-bold uppercase text-slate-500">Collection / Solution</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="collection_id">
                        <option value="">Choose collection</option>
                        @foreach ($collections as $collection)
                            <option value="{{ $collection->id }}" @selected((string) $selectedCollectionId === (string) $collection->id)>{{ $collection->name }} - {{ str($collection->type)->headline() }} ({{ $collection->slug }})</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Best for solution/campaign groups such as Home Backup or Summer Sale.</span>
                </label>
            </div>

            <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4" data-manual-source>
                <div>
                    <p class="text-xs font-black uppercase text-slate-500">Manual Products</p>
                    <p class="mt-1 text-sm font-semibold text-slate-600">Search products, add them to this section, then adjust the order. This does not duplicate product records.</p>
                </div>

                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_140px]">
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="search" placeholder="Search product name, slug, or description" data-product-search>
                    <button class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-bold" type="button" data-product-search-button>Search</button>
                </div>
                <div class="hidden rounded-md border border-slate-200 bg-white p-2" data-product-results></div>

                <div class="grid gap-2" data-selected-products>
                    @foreach ($selectedProducts as $index => $product)
                        <div class="grid gap-3 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_110px_90px_90px] md:items-center" data-product-row data-product-id="{{ $product->id }}">
                            <input type="hidden" name="products[{{ $index }}][id]" value="{{ $product->id }}" data-product-id-input>
                            <div>
                                <p class="font-bold">{{ $product->name }}</p>
                                <p class="text-xs font-semibold text-slate-500">{{ $product->category?->name ?: 'No primary category' }} / {{ $product->slug }}</p>
                            </div>
                            <label class="grid gap-1">
                                <span class="text-xs font-bold uppercase text-slate-500">Order</span>
                                <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="products[{{ $index }}][sort_order]" value="{{ old("products.{$index}.sort_order", $product->pivot->sort_order ?? $index + 1) }}" min="0" data-product-order>
                            </label>
                            <label class="flex items-center gap-2 text-sm font-bold">
                                <input type="hidden" name="products[{{ $index }}][active]" value="0" data-product-active-hidden>
                                <input type="checkbox" name="products[{{ $index }}][active]" value="1" @checked(old("products.{$index}.active", $product->pivot->active ?? true)) data-product-active>
                                Active
                            </label>
                            <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-bold text-red-700" type="button" data-remove-product>Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Side Redirect Cards</p>
                <h2 class="mt-1 text-xl font-black">Right-side cards</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">These cards redirect users to a full category, collection, or custom page.</p>
            </div>

            <div class="grid gap-4">
                @foreach ($cardRows as $index => $card)
                    @include('admin.homepage-content.partials.mixed-navigation-card', ['card' => $card, 'index' => $index])
                @endforeach

                @for ($i = 0; $i < $blankCardCount; $i++)
                    @include('admin.homepage-content.partials.mixed-navigation-card', ['card' => null, 'index' => $cardRows->count() + $i])
                @endfor
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Feature Banner</p>
                <h2 class="mt-1 text-xl font-black">Large banner below this product grid</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Use an image or video. If video exists, the frontend can use it as the moving background with image fallback.</p>
            </div>

            <input type="hidden" name="banner[id]" value="{{ old('banner.id', $featureBanner?->id) }}">

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Heading Above Banner</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[section_heading]" value="{{ old('banner.section_heading', $featureBanner?->section_heading) }}">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Promo Label</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[eyebrow]" value="{{ old('banner.eyebrow', $featureBanner?->eyebrow) }}">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Banner Title</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[title]" value="{{ old('banner.title', $featureBanner?->title) }}">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Price / Offer Text</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[price_text]" value="{{ old('banner.price_text', $featureBanner?->price_text) }}">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Banner Subtitle</span>
                    <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="banner[subtitle]">{{ old('banner.subtitle', $featureBanner?->subtitle) }}</textarea>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Text</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[button_text]" value="{{ old('banner.button_text', $featureBanner?->button_text) }}">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Link</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner[button_link]" value="{{ old('banner.button_link', $featureBanner?->button_link) }}">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Text Color</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="banner[text_color]">
                        <option value="light" @selected(old('banner.text_color', $featureBanner?->text_color ?: 'light') === 'light')>Light text</option>
                        <option value="dark" @selected(old('banner.text_color', $featureBanner?->text_color) === 'dark')>Dark text</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Text Alignment</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="banner[text_alignment]">
                        <option value="left" @selected(old('banner.text_alignment', $featureBanner?->text_alignment ?: 'left') === 'left')>Left</option>
                        <option value="center" @selected(old('banner.text_alignment', $featureBanner?->text_alignment) === 'center')>Center</option>
                        <option value="right" @selected(old('banner.text_alignment', $featureBanner?->text_alignment) === 'right')>Right</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Banner Sort Order</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="banner[sort_order]" value="{{ old('banner.sort_order', $featureBanner?->sort_order ?? 1) }}" min="0">
                </label>

                <label class="flex items-center gap-2 self-end text-sm font-bold">
                    <input type="hidden" name="banner[active]" value="0">
                    <input type="checkbox" name="banner[active]" value="1" @checked(old('banner.active', $featureBanner?->active ?? true))>
                    Banner active
                </label>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Desktop Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="banner[background_image]" accept="image/*">
                    @if ($featureBanner?->background_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $featureBanner->background_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="banner[background_image_remove]" value="1">
                            Remove desktop image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Mobile Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="banner[mobile_background_image]" accept="image/*">
                    @if ($featureBanner?->mobile_background_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $featureBanner->mobile_background_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="banner[mobile_background_image_remove]" value="1">
                            Remove mobile image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Optional Video</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="banner[background_video]" accept="video/mp4,video/webm,video/ogg">
                    @if ($featureBanner?->background_video_url)
                        <video class="h-36 w-full max-w-md rounded-md bg-slate-950 object-cover" src="{{ $featureBanner->background_video_url }}" controls muted></video>
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="banner[background_video_remove]" value="1">
                            Remove video
                        </label>
                    @endif
                </section>
            </div>
        </section>

        <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/95 p-4 backdrop-blur sm:-mx-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.homepage-content.index') }}">Cancel</a>
                <button class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Save Mixed Showcase</button>
            </div>
        </div>
    </form>

    <template data-product-template>
        <div class="grid gap-3 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-[minmax(0,1fr)_110px_90px_90px] md:items-center" data-product-row>
            <input type="hidden" data-product-id-input>
            <div>
                <p class="font-bold" data-product-name></p>
                <p class="text-xs font-semibold text-slate-500" data-product-meta></p>
            </div>
            <label class="grid gap-1">
                <span class="text-xs font-bold uppercase text-slate-500">Order</span>
                <input class="rounded-md border border-slate-300 px-3 py-2" type="number" data-product-order min="0">
            </label>
            <label class="flex items-center gap-2 text-sm font-bold">
                <input type="hidden" data-product-active-hidden value="0">
                <input type="checkbox" data-product-active value="1" checked>
                Active
            </label>
            <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-bold text-red-700" type="button" data-remove-product>Remove</button>
        </div>
    </template>

    <script>
        (() => {
            const sourceType = document.querySelector('[data-source-type]');
            const categorySource = document.querySelector('[data-category-source]');
            const collectionSource = document.querySelector('[data-collection-source]');
            const manualSource = document.querySelector('[data-manual-source]');
            const searchInput = document.querySelector('[data-product-search]');
            const searchButton = document.querySelector('[data-product-search-button]');
            const results = document.querySelector('[data-product-results]');
            const selectedProducts = document.querySelector('[data-selected-products]');
            const template = document.querySelector('[data-product-template]');

            const updateSourceVisibility = () => {
                const value = sourceType?.value;
                categorySource?.classList.toggle('hidden', value !== 'category');
                collectionSource?.classList.toggle('hidden', value !== 'collection');
                manualSource?.classList.toggle('hidden', value !== 'manual_products');
            };

            const selectedIds = () => Array.from(selectedProducts?.querySelectorAll('[data-product-row]') ?? [])
                .map((row) => row.getAttribute('data-product-id'))
                .filter(Boolean);

            const refreshProductNames = () => {
                selectedProducts?.querySelectorAll('[data-product-row]').forEach((row, index) => {
                    const id = row.getAttribute('data-product-id');
                    row.querySelector('[data-product-id-input]')?.setAttribute('name', `products[${index}][id]`);
                    row.querySelector('[data-product-order]')?.setAttribute('name', `products[${index}][sort_order]`);
                    row.querySelector('[data-product-active-hidden]')?.setAttribute('name', `products[${index}][active]`);
                    row.querySelector('[data-product-active]')?.setAttribute('name', `products[${index}][active]`);
                    row.querySelector('[data-product-id-input]')?.setAttribute('value', id ?? '');
                });
            };

            const addProduct = (product) => {
                if (! selectedProducts || ! template || selectedIds().includes(String(product.id))) {
                    return;
                }

                const row = template.content.firstElementChild.cloneNode(true);
                const nextIndex = selectedProducts.querySelectorAll('[data-product-row]').length;
                row.setAttribute('data-product-id', product.id);
                row.querySelector('[data-product-name]').textContent = product.name;
                row.querySelector('[data-product-meta]').textContent = `${product.category || 'No primary category'} / ${product.slug}`;
                row.querySelector('[data-product-id-input]').value = product.id;
                row.querySelector('[data-product-order]').value = nextIndex + 1;
                selectedProducts.appendChild(row);
                refreshProductNames();
            };

            const runSearch = async () => {
                if (! results) return;
                results.classList.remove('hidden');
                results.innerHTML = '<p class="p-2 text-sm font-semibold text-slate-500">Searching...</p>';

                const params = new URLSearchParams({
                    query: searchInput?.value ?? '',
                    selected: selectedIds().join(','),
                });

                const response = await fetch(`{{ route('admin.product-picker') }}?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                const products = await response.json();

                if (! products.length) {
                    results.innerHTML = '<p class="p-2 text-sm font-semibold text-slate-500">No products found.</p>';
                    return;
                }

                results.innerHTML = '';
                products.forEach((product) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-left text-sm font-semibold hover:bg-slate-50';
                    button.innerHTML = `<span><strong>${product.name}</strong><br><small class="text-slate-500">${product.category || 'No primary category'} / ${product.slug}</small></span><span class="rounded-md border border-slate-300 px-3 py-1 text-xs font-bold">Add</span>`;
                    button.addEventListener('click', () => addProduct(product));
                    results.appendChild(button);
                });
            };

            sourceType?.addEventListener('change', updateSourceVisibility);
            searchButton?.addEventListener('click', runSearch);
            searchInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    runSearch();
                }
            });
            selectedProducts?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-remove-product]');
                if (! button) return;
                button.closest('[data-product-row]')?.remove();
                refreshProductNames();
            });

            updateSourceVisibility();
            refreshProductNames();
        })();
    </script>
@endsection
