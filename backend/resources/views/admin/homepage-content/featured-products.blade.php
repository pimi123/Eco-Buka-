@extends('admin.layout', ['title' => 'Edit Featured Product Section'])

@php
    $selectedCategory = $section->source_type === 'category'
        ? $categories->first(fn ($category) => (string) $category->id === (string) $section->source_id || $category->slug === $section->source_slug)
        : null;
    $selectedCollection = $section->source_type === 'collection'
        ? $collections->first(fn ($collection) => (string) $collection->id === (string) $section->source_id || $collection->slug === $section->source_slug)
        : null;
    $selectedCategoryId = old('category_id', $selectedCategory?->id);
    $selectedCollectionId = old('collection_id', $selectedCollection?->id);
@endphp

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.homepage-content.index') }}">Back to Homepage Content Display</a>
            <h1 class="mt-2 text-2xl font-black">Edit Banner + Product Grid</h1>
            <p class="mt-2 max-w-4xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
                This section promotes one category or collection with a large banner, optional video, and a product grid. Products are loaded dynamically from the selected source.
            </p>
        </div>
    </div>

    <form class="mt-5 grid gap-5" method="post" enctype="multipart/form-data" action="{{ route('admin.homepage-content.featured-products.update', $section) }}">
        @csrf
        @method('put')

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Section Content</p>
                <h2 class="mt-1 text-xl font-black">{{ $section->title ?: str($section->section_key)->headline() }}</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Internal key: {{ $section->section_key }}</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Title</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="title" value="{{ old('title', $section->title) }}" placeholder="Power Stations">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Top Label</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="eyebrow" value="{{ old('eyebrow', $section->eyebrow ?: 'Featured Category') }}" placeholder="Featured Category">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Subtitle</span>
                    <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="subtitle" placeholder="Short text above the banner.">{{ old('subtitle', $section->subtitle) }}</textarea>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Homepage Position</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" min="0">
                </label>

                <label class="flex items-center gap-2 self-end text-sm font-bold">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" @checked(old('active', $section->active))>
                    Enabled on homepage
                </label>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Banner Content and Media</p>
                <h2 class="mt-1 text-xl font-black">Banner</h2>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Banner Title</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="banner_title" value="{{ old('banner_title', $section->banner_title) }}" placeholder="Power Stations">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Text</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="button_text" value="{{ old('button_text', $section->button_text ?: 'Learn More') }}" placeholder="Learn More">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Banner Subtitle</span>
                    <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="banner_subtitle" placeholder="Text shown over the banner.">{{ old('banner_subtitle', $section->banner_subtitle) }}</textarea>
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Link / Redirect</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="button_link" value="{{ old('button_link', $section->button_link) }}" placeholder="Leave empty to auto-link to selected category or collection">
                </label>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Desktop Banner Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="banner_image" accept="image/*">
                    @if ($section->banner_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $section->banner_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="banner_image_remove" value="1">
                            Remove desktop image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Mobile Banner Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="mobile_banner_image" accept="image/*">
                    @if ($section->mobile_banner_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $section->mobile_banner_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="mobile_banner_image_remove" value="1">
                            Remove mobile image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Optional Background Video</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="background_video" accept="video/mp4,video/webm,video/ogg">
                    @if ($section->background_video_url)
                        <video class="h-36 w-full max-w-md rounded-md bg-slate-950 object-cover" src="{{ $section->background_video_url }}" controls muted></video>
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="background_video_remove" value="1">
                            Remove video
                        </label>
                    @endif
                    <p class="text-xs font-semibold text-slate-500">The frontend will autoplay this muted and looped. Keep videos optimized for performance.</p>
                </section>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Product Source</p>
                <h2 class="mt-1 text-xl font-black">Dynamic Products</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Choose a category or collection. The first products from that source will appear in the grid.</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Source Type</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="source_type" data-source-type>
                        <option value="category" @selected(old('source_type', $section->source_type ?: 'category') === 'category')>Category / product-based group</option>
                        <option value="collection" @selected(old('source_type', $section->source_type) === 'collection')>Collection / solution-based group</option>
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Display Limit</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="display_limit" value="{{ old('display_limit', $section->display_limit ?: 8) }}" min="1" max="24">
                </label>

                <label class="grid gap-2" data-category-source>
                    <span class="text-xs font-bold uppercase text-slate-500">Category</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="category_id">
                        <option value="">Choose category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }} ({{ $category->slug }})</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Use this for product type or series groups like Power Stations, Solar Panels, DELTA Series.</span>
                </label>

                <label class="grid gap-2" data-collection-source>
                    <span class="text-xs font-bold uppercase text-slate-500">Collection / Solution</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="collection_id">
                        <option value="">Choose collection</option>
                        @foreach ($collections as $collection)
                            <option value="{{ $collection->id }}" @selected((string) $selectedCollectionId === (string) $collection->id)>{{ $collection->name }} - {{ str($collection->type)->headline() }} ({{ $collection->slug }})</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Use this for service/solution groups like Home Backup, Summer Sale, Popular Products.</span>
                </label>
            </div>
        </section>

        <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/95 p-4 backdrop-blur sm:-mx-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.homepage-content.index') }}">Cancel</a>
                <button class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Save Section</button>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const sourceType = document.querySelector('[data-source-type]');
            const categorySource = document.querySelector('[data-category-source]');
            const collectionSource = document.querySelector('[data-collection-source]');

            const updateSourceVisibility = () => {
                const isCollection = sourceType?.value === 'collection';
                categorySource?.classList.toggle('hidden', isCollection);
                collectionSource?.classList.toggle('hidden', !isCollection);
            };

            sourceType?.addEventListener('change', updateSourceVisibility);
            updateSourceVisibility();
        })();
    </script>
@endsection
