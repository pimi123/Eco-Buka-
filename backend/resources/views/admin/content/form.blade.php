@extends('admin.layout', ['title' => ($item->exists ? 'Edit ' : 'Add ').$config['label']])

@section('content')
    <h1 class="text-2xl font-black">{{ $item->exists ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
    @if (!empty($config['description']))
        <p class="mt-2 max-w-3xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
            {{ $config['description'] }}
        </p>
    @endif
    @if ($resource === 'showcase-sections')
        <div class="mt-3 max-w-4xl rounded-lg border border-amber-100 bg-amber-50 p-3 text-sm font-semibold leading-6 text-amber-950" data-section-type-help>
            Choose a section type first. The form will show only the settings that make sense for that homepage feature.
        </div>
    @endif
    <form id="content-form" class="mt-5 grid gap-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.content.update', [$resource, $item]) : route('admin.content.store', $resource) }}">
        @csrf
        @if ($item->exists)
            @method('put')
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($config['fields'] as $field => $type)
                @php
                    $rawValue = $item->{$field};
                    if ($type === 'json' && is_array($rawValue)) {
                        $jsonLines = [];
                        foreach ($rawValue as $specKey => $specValue) {
                            $jsonLines[] = is_string($specKey)
                                ? $specKey.': '.(is_scalar($specValue) ? $specValue : json_encode($specValue))
                                : (is_scalar($specValue) ? $specValue : json_encode($specValue));
                        }
                        $rawValue = implode("\n", $jsonLines);
                    }
                    $value = old($field, $type === 'json' ? $rawValue : $item->{$field});
                    $fieldHelp = [
                        'category_id' => 'Primary technical category used for breadcrumbs and default catalogue grouping.',
                        'category_ids' => 'Extra technical groups. Example: Power Stations + RIVER Series.',
                        'collection_ids' => 'Flexible marketing groups. Example: Home Backup, Summer Sale, Popular Products.',
                        'product_ids' => 'Products attached to this collection. This does not duplicate products.',
                        'homepage_section_id' => 'Choose the homepage section where this card should appear. This replaces typing hidden keys like new_products.',
                        'type' => 'Solution = customer use case, Campaign = timed sale, Merchandising = curated group, Featured = highlighted group.',
                        'section_type' => 'Controls how the homepage block is displayed: product grid, carousel, promo cards, video, etc.',
                        'source_type' => 'Controls where section products/cards come from: manual products, category, collection, or manual cards.',
                        'source_id' => 'Optional numeric ID for the chosen category or collection source.',
                        'source_slug' => 'Optional slug for the chosen category or collection source, for example home-backup.',
                        'display_limit' => 'Maximum number of products/cards this section should show.',
                        'in_stock' => 'If unchecked, the product stays visible but customers cannot add it to cart or checkout with it.',
                    ][$field] ?? null;
                @endphp
                <div class="[display:contents]" data-homepage-section-field="{{ $field }}">
                @if ($type === 'boolean')
                    <label class="flex items-center gap-2 text-sm font-semibold">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item->exists ? $item->{$field} : true))>
                        {{ str($field)->headline() }}
                    </label>
                @elseif ($type === 'textarea' || $type === 'json')
                    <label class="grid gap-2 md:col-span-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" @if($fieldHelp) title="{{ $fieldHelp }}" @endif>
                            {{ str($field)->headline() }}
                            @if($fieldHelp)<span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>@endif
                        </span>
                        <textarea class="min-h-28 rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">{{ $value }}</textarea>
                        @if ($fieldHelp)
                            <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                        @endif
                        @if ($type === 'json')
                            <span class="text-xs font-semibold text-slate-500">
                                @if ($field === 'downloads')
                                    Use one download per line, for example: Manual: https://example.com/manual.pdf
                                @elseif ($field === 'included_items')
                                    Use one included item per line, for example: Charging cable
                                @else
                                    Use one spec per line, for example: Capacity: 2kWh
                                @endif
                            </span>
                        @endif
                    </label>
                @elseif ($type === 'image')
                    @php($previewUrl = $item->{$field.'_url'} ?? ($item->{$field} ? Storage::disk('public')->url($item->{$field}) : null))
                    <section class="grid gap-2">
                        <div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" @if($fieldHelp) title="{{ $fieldHelp }}" @endif>
                                {{ str($field)->headline() }}
                                @if($fieldHelp)<span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>@endif
                            </span>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Upload a new image to replace the current one, or remove the current image intentionally.</p>
                        </div>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="{{ $field }}" accept="image/*">
                        @if ($previewUrl)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3" data-image-item>
                                <img class="h-24 w-32 rounded-md bg-white object-contain p-2" src="{{ $previewUrl }}" alt="">
                                <label class="mt-3 inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700">
                                    <input type="checkbox" name="{{ $field }}_remove" value="1" data-image-remove>
                                    Remove current image
                                </label>
                            </div>
                        @endif
                    </section>
                @elseif ($type === 'video')
                    @php($previewUrl = $item->{$field.'_url'} ?? ($item->{$field} ? Storage::disk('public')->url($item->{$field}) : null))
                    <label class="grid gap-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="{{ $field }}" accept="video/mp4,video/webm,video/ogg">
                        @if ($previewUrl)
                            <video class="h-32 w-full max-w-sm rounded-md bg-slate-950 object-cover" src="{{ $previewUrl }}" muted controls></video>
                            <label class="mt-2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700">
                                <input type="checkbox" name="{{ $field }}_remove" value="1">
                                Remove current video
                            </label>
                        @endif
                    </label>
                @elseif ($type === 'gallery')
                    @php($galleryImages = collect($item->{$field} ?? [])->filter()->values())
                    <section class="grid gap-3 md:col-span-2">
                        <div>
                            <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Remove individual images, reorder the current gallery, or upload new images to append them.</p>
                        </div>

                        @if ($galleryImages->isNotEmpty())
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4" data-gallery-list="{{ $field }}">
                                @foreach ($galleryImages as $galleryImage)
                                    @php($galleryUrl = Storage::disk('public')->url($galleryImage))
                                    <div class="gallery-item rounded-lg border border-slate-200 bg-slate-50 p-3" data-gallery-item>
                                        <input type="hidden" name="{{ $field }}_existing[]" value="{{ $galleryImage }}">
                                        <label class="block">
                                            <img class="h-32 w-full rounded-md bg-white object-contain p-2" src="{{ $galleryUrl }}" alt="">
                                            <span class="mt-2 block break-all text-xs font-semibold text-slate-500">{{ basename($galleryImage) }}</span>
                                        </label>
                                        <div class="mt-3 grid grid-cols-2 gap-2">
                                            <button class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold" type="button" data-gallery-move="up">Move up</button>
                                            <button class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold" type="button" data-gallery-move="down">Move down</button>
                                        </div>
                                        <label class="mt-3 flex items-center gap-2 rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700">
                                            <input type="checkbox" name="{{ $field }}_remove[]" value="{{ $galleryImage }}" data-gallery-remove>
                                            Remove this image
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                                No gallery images yet.
                            </div>
                        @endif

                        <label class="grid gap-2">
                            <span class="text-xs font-bold uppercase text-slate-500">Add new gallery images</span>
                            <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="{{ $field }}[]" accept="image/*" multiple>
                            <span class="text-xs font-semibold text-slate-500">New uploads are appended after the remaining existing images.</span>
                        </label>
                    </section>
                @elseif ($type === 'category')
                    <label class="grid gap-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Category
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            <option value="">No category</option>
                            @foreach ($options['categories'] as $id => $name)
                                <option value="{{ $id }}" @selected((string) $value === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                    </label>
                @elseif ($type === 'homepage_section')
                    <label class="grid gap-2 md:col-span-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Homepage Section
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            <option value="">No homepage section</option>
                            @foreach ($options['homepageSections'] as $sectionOption)
                                <option value="{{ $sectionOption->id }}" @selected((string) old($field, $item->{$field}) === (string) $sectionOption->id)>
                                    {{ $sectionOption->title ?: str($sectionOption->section_key)->headline() }} - {{ str($sectionOption->section_type ?: 'section')->headline() }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                    </label>
                @elseif ($type === 'category_multi')
                    @php($selectedCategories = collect(old($field, $item->categories?->pluck('id')->all() ?? []))->map(fn($id) => (string) $id))
                    <section class="grid gap-2 md:col-span-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Additional Categories
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <p class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</p>
                        <div class="grid gap-2 rounded-md border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($options['categories'] as $id => $name)
                                <label class="flex items-center gap-2 text-sm font-semibold">
                                    <input type="checkbox" name="{{ $field }}[]" value="{{ $id }}" @checked($selectedCategories->contains((string) $id))>
                                    {{ $name }}
                                </label>
                            @endforeach
                        </div>
                    </section>
                @elseif ($type === 'collection_multi')
                    @php($selectedCollections = collect(old($field, $item->collections?->pluck('id')->all() ?? []))->map(fn($id) => (string) $id))
                    <section class="grid gap-2 md:col-span-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Collections / Solutions / Campaigns
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <p class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</p>
                        <div class="grid gap-2 rounded-md border border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($options['collections'] as $collection)
                                <label class="flex items-center gap-2 text-sm font-semibold">
                                    <input type="checkbox" name="{{ $field }}[]" value="{{ $collection->id }}" @checked($selectedCollections->contains((string) $collection->id))>
                                    <span>{{ $collection->name }} <span class="text-xs text-slate-500">({{ $collection->type }})</span></span>
                                </label>
                            @endforeach
                        </div>
                    </section>
                @elseif ($type === 'product_multi')
                    @php($selectedProducts = collect($item->products ?? [])->sortBy(fn($product) => $product->pivot?->sort_order ?? 0)->values())
                    <section class="grid gap-2 md:col-span-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Products In This Collection
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <p class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</p>
                        <div class="rounded-md border border-slate-200 p-3" data-product-picker data-search-url="{{ route('admin.product-picker') }}" data-field="{{ $field }}">
                            <div class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
                                <label class="grid gap-1">
                                    <span class="text-xs font-bold uppercase text-slate-500">Search Products</span>
                                    <input class="rounded-md border border-slate-300 px-3 py-2" type="search" placeholder="Search by product name, slug, or description" data-product-picker-search>
                                </label>
                                <button class="self-end rounded-md border border-slate-300 px-4 py-2 text-sm font-bold" type="button" data-product-picker-clear>Clear</button>
                            </div>

                            <div class="mt-3 hidden rounded-md border border-slate-200 bg-slate-50 p-2" data-product-picker-results></div>

                            <div class="mt-4">
                                <h3 class="text-xs font-bold uppercase text-slate-500">Selected Products</h3>
                                <div class="mt-2 grid gap-2" data-product-picker-selected>
                                    @foreach ($selectedProducts as $product)
                                        <div class="grid gap-2 rounded-md border border-slate-200 bg-white p-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center" data-product-picker-item data-product-id="{{ $product->id }}">
                                            <input type="hidden" name="{{ $field }}[]" value="{{ $product->id }}">
                                            <div>
                                                <p class="text-sm font-bold">{{ $product->name }}</p>
                                                <p class="text-xs font-semibold text-slate-500">{{ $product->category?->name ?: 'No category' }} @if(!$product->active) - Inactive @endif</p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <button class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold" type="button" data-product-picker-move="up">Up</button>
                                                <button class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold" type="button" data-product-picker-move="down">Down</button>
                                                <button class="rounded-md border border-red-200 px-3 py-2 text-xs font-bold text-red-700" type="button" data-product-picker-remove>Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs font-semibold text-slate-500" data-product-picker-empty @class(['hidden' => $selectedProducts->isNotEmpty()])>
                                    No products selected yet. Search above and add products to this collection.
                                </p>
                            </div>
                        </div>
                    </section>
                @elseif ($type === 'collection_type')
                    <label class="grid gap-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Collection Type
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            @foreach (['solution' => 'Solution', 'campaign' => 'Campaign', 'merchandising' => 'Merchandising', 'featured' => 'Featured'] as $optionValue => $label)
                                <option value="{{ $optionValue }}" @selected((string) old($field, $item->{$field} ?: 'merchandising') === $optionValue)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                    </label>
                @elseif ($type === 'section_type')
                    <label class="grid gap-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Section Type
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            @foreach (['product_grid' => 'Product Grid', 'product_carousel' => 'Product Carousel', 'promo_cards' => 'Promo Cards', 'category_carousel' => 'Category Carousel', 'hero_banner' => 'Hero Banner', 'video_banner' => 'Video Banner', 'mixed_showcase' => 'Mixed Showcase'] as $optionValue => $label)
                                <option value="{{ $optionValue }}" @selected((string) old($field, $item->{$field} ?: 'product_grid') === $optionValue)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                    </label>
                @elseif ($type === 'source_type')
                    <label class="grid gap-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" title="{{ $fieldHelp }}">
                            Source Type
                            <span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>
                        </span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            @foreach (['manual_products' => 'Manual Products', 'category' => 'Category', 'collection' => 'Collection', 'manual_cards' => 'Manual Cards'] as $optionValue => $label)
                                <option value="{{ $optionValue }}" @selected((string) old($field, $item->{$field} ?: 'manual_products') === $optionValue)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                    </label>
                @else
                    <label class="grid gap-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold uppercase text-slate-500" @if($fieldHelp) title="{{ $fieldHelp }}" @endif>
                            {{ str($field)->headline() }}
                            @if($fieldHelp)<span class="grid h-4 w-4 place-items-center rounded-full bg-slate-200 text-[10px] text-slate-600">?</span>@endif
                        </span>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="{{ in_array($type, ['price', 'number']) ? 'number' : 'text' }}" step="{{ $type === 'price' ? '0.01' : '1' }}" name="{{ $field }}" value="{{ $value }}">
                        @if ($fieldHelp)
                            <span class="text-xs font-semibold text-slate-500">{{ $fieldHelp }}</span>
                        @endif
                    </label>
                @endif
                </div>
            @endforeach
        </div>

        @if ($resource === 'showcase-sections')
            <section class="rounded-lg border border-slate-200 p-4" data-homepage-products-panel>
                <h2 class="font-black">Products in this section</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Used only for product sections with Manual Products source. Category and collection sections can load products automatically.</p>
                <div class="mt-3 grid gap-3">
                    @foreach ($options['products'] as $product)
                        @php($pivot = $item->products?->firstWhere('id', $product->id)?->pivot)
                        <div class="grid gap-2 rounded-md border border-slate-200 p-3 md:grid-cols-[1fr_120px_100px]">
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input type="checkbox" name="products[{{ $product->id }}][active]" value="1" @checked($pivot?->active)>
                                {{ $product->name }}
                            </label>
                            <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="products[{{ $product->id }}][sort_order]" value="{{ $pivot?->sort_order ?? 0 }}">
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row">
            <button id="content-submit" class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white disabled:cursor-wait disabled:opacity-70">Save {{ $config['label'] }}</button>
            <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.content.index', $resource) }}">Back</a>
        </div>
    </form>

    <script>
        document.querySelectorAll('[data-gallery-remove]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if (checkbox.checked && !window.confirm('Remove this image from the gallery when you save?')) {
                    checkbox.checked = false;
                    return;
                }

                checkbox.closest('[data-gallery-item]')?.classList.toggle('opacity-50', checkbox.checked);
            });
        });

        document.querySelectorAll('[data-image-remove]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                if (checkbox.checked && !window.confirm('Remove this image when you save?')) {
                    checkbox.checked = false;
                    return;
                }

                checkbox.closest('[data-image-item]')?.classList.toggle('opacity-50', checkbox.checked);
            });
        });

        document.querySelectorAll('[data-gallery-move]').forEach((button) => {
            button.addEventListener('click', () => {
                const item = button.closest('[data-gallery-item]');
                const list = item?.parentElement;
                if (!item || !list) return;

                if (button.dataset.galleryMove === 'up' && item.previousElementSibling) {
                    list.insertBefore(item, item.previousElementSibling);
                }

                if (button.dataset.galleryMove === 'down' && item.nextElementSibling) {
                    list.insertBefore(item.nextElementSibling, item);
                }
            });
        });

        document.querySelectorAll('[data-product-picker]').forEach((picker) => {
            const searchInput = picker.querySelector('[data-product-picker-search]');
            const results = picker.querySelector('[data-product-picker-results]');
            const selectedList = picker.querySelector('[data-product-picker-selected]');
            const emptyState = picker.querySelector('[data-product-picker-empty]');
            const clearButton = picker.querySelector('[data-product-picker-clear]');
            const searchUrl = picker.dataset.searchUrl;
            const fieldName = picker.dataset.field || 'product_ids';
            let searchTimer;

            const selectedIds = () => Array.from(selectedList?.querySelectorAll('[data-product-picker-item]') || [])
                .map((item) => item.dataset.productId);

            const updateEmptyState = () => {
                emptyState?.classList.toggle('hidden', selectedIds().length > 0);
            };

            const createProductRow = (product) => {
                const row = document.createElement('div');
                row.className = 'grid gap-2 rounded-md border border-slate-200 bg-white p-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center';
                row.dataset.productPickerItem = '';
                row.dataset.productId = String(product.id);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `${fieldName}[]`;
                input.value = String(product.id);

                const copy = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'text-sm font-bold';
                title.textContent = product.name;
                const meta = document.createElement('p');
                meta.className = 'text-xs font-semibold text-slate-500';
                meta.textContent = `${product.category || 'No category'}${product.active ? '' : ' - Inactive'}`;
                copy.append(title, meta);

                const actions = document.createElement('div');
                actions.className = 'flex flex-wrap gap-2';

                const up = document.createElement('button');
                up.className = 'rounded-md border border-slate-300 px-3 py-2 text-xs font-bold';
                up.type = 'button';
                up.dataset.productPickerMove = 'up';
                up.textContent = 'Up';

                const down = document.createElement('button');
                down.className = 'rounded-md border border-slate-300 px-3 py-2 text-xs font-bold';
                down.type = 'button';
                down.dataset.productPickerMove = 'down';
                down.textContent = 'Down';

                const remove = document.createElement('button');
                remove.className = 'rounded-md border border-red-200 px-3 py-2 text-xs font-bold text-red-700';
                remove.type = 'button';
                remove.dataset.productPickerRemove = '';
                remove.textContent = 'Remove';

                actions.append(up, down, remove);
                row.append(input, copy, actions);
                return row;
            };

            const renderResults = (products) => {
                if (!results) return;

                results.innerHTML = '';
                results.classList.remove('hidden');

                if (!products.length) {
                    const empty = document.createElement('p');
                    empty.className = 'p-2 text-sm font-semibold text-slate-500';
                    empty.textContent = 'No matching products found.';
                    results.append(empty);
                    return;
                }

                const currentIds = selectedIds();
                products.forEach((product) => {
                    const row = document.createElement('div');
                    row.className = 'flex flex-col gap-2 border-b border-slate-200 p-2 last:border-b-0 sm:flex-row sm:items-center sm:justify-between';

                    const copy = document.createElement('div');
                    const title = document.createElement('p');
                    title.className = 'text-sm font-bold';
                    title.textContent = product.name;
                    const meta = document.createElement('p');
                    meta.className = 'text-xs font-semibold text-slate-500';
                    meta.textContent = `${product.category || 'No category'}${product.price ? ` - EUR ${product.price}` : ''}`;
                    copy.append(title, meta);

                    const add = document.createElement('button');
                    const alreadySelected = currentIds.includes(String(product.id));
                    add.className = 'rounded-md border border-slate-300 px-3 py-2 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50';
                    add.type = 'button';
                    add.textContent = alreadySelected ? 'Added' : 'Add';
                    add.disabled = alreadySelected;
                    add.addEventListener('click', () => {
                        if (!selectedList || selectedIds().includes(String(product.id))) return;

                        selectedList.append(createProductRow(product));
                        updateEmptyState();
                        add.textContent = 'Added';
                        add.disabled = true;
                    });

                    row.append(copy, add);
                    results.append(row);
                });
            };

            const searchProducts = async () => {
                if (!searchUrl || !searchInput) return;
                const query = searchInput.value.trim();

                if (query.length < 2) {
                    results?.classList.add('hidden');
                    return;
                }

                results?.classList.remove('hidden');
                if (results) {
                    results.innerHTML = '<p class="p-2 text-sm font-semibold text-slate-500">Searching...</p>';
                }

                try {
                    const response = await fetch(`${searchUrl}?query=${encodeURIComponent(query)}`, {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) throw new Error('Search failed');
                    renderResults(await response.json());
                } catch {
                    if (results) {
                        results.innerHTML = '<p class="p-2 text-sm font-semibold text-red-700">Could not search products. Please try again.</p>';
                    }
                }
            };

            searchInput?.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(searchProducts, 250);
            });

            clearButton?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                results?.classList.add('hidden');
                if (results) results.innerHTML = '';
            });

            selectedList?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) return;

                const item = target.closest('[data-product-picker-item]');
                if (!item || !selectedList) return;

                if (target.closest('[data-product-picker-remove]')) {
                    if (window.confirm('Remove this product from the collection when you save?')) {
                        item.remove();
                        updateEmptyState();
                    }
                    return;
                }

                const moveButton = target.closest('[data-product-picker-move]');
                if (!moveButton) return;

                if (moveButton.dataset.productPickerMove === 'up' && item.previousElementSibling) {
                    selectedList.insertBefore(item, item.previousElementSibling);
                }

                if (moveButton.dataset.productPickerMove === 'down' && item.nextElementSibling) {
                    selectedList.insertBefore(item.nextElementSibling, item);
                }
            });

            updateEmptyState();
        });

        (() => {
            const sectionType = document.querySelector('select[name="section_type"]');
            if (!sectionType) return;

            const sourceType = document.querySelector('select[name="source_type"]');
            const help = document.querySelector('[data-section-type-help]');
            const productsPanel = document.querySelector('[data-homepage-products-panel]');
            const fieldGroups = {
                shared: ['section_key', 'title', 'subtitle', 'section_type', 'active', 'sort_order'],
                product: ['source_type', 'source_id', 'source_slug', 'display_limit', 'layout_variant'],
                featured: ['source_type', 'source_id', 'source_slug', 'display_limit', 'layout_variant', 'banner_image', 'mobile_banner_image', 'button_text', 'button_link'],
                promo: ['display_limit', 'layout_variant'],
                video: [],
            };
            const helpText = {
                promo_cards: 'Promo Section: use Homepage Content Display to manage layout, title, and cards from one focused screen.',
                featured_category: 'Featured Category/Product Section: choose category or collection source, optional banner image, CTA, and display limit.',
                mixed_showcase: 'Product Showcase: choose products/source here; side cards live in Navigation Cards and the lower feature banner lives in Feature Banners.',
                product_grid: 'Product Grid: choose category, collection, or manual products. This does not use promo-card image settings.',
                product_carousel: 'Product Carousel: choose category, collection, or manual products. This does not use promo-card image settings.',
                video_banner: 'Video Banner: only section name/order lives here. Video, fallback image, headline, and CTA live in Feature Banners.',
                category_carousel: 'Category Carousel: controlled by Categories, not product or promo-card settings.',
                hero_banner: 'Hero Slider: controlled by Hero Banners, not product or promo-card settings.',
            };

            const setSourceForType = (type) => {
                if (!sourceType) return;
                if (type === 'promo_cards' || type === 'video_banner' || type === 'hero_banner' || type === 'category_carousel') {
                    sourceType.value = 'manual_cards';
                }
            };

            const showFields = () => {
                const type = sectionType.value || 'product_grid';
                setSourceForType(type);

                const visible = new Set(fieldGroups.shared);

                if (['product_grid', 'product_carousel', 'mixed_showcase'].includes(type)) {
                    fieldGroups.product.forEach((field) => visible.add(field));
                }

                if (type === 'featured_category') {
                    fieldGroups.featured.forEach((field) => visible.add(field));
                }

                if (type === 'promo_cards') {
                    fieldGroups.promo.forEach((field) => visible.add(field));
                }

                if (type === 'video_banner') {
                    fieldGroups.video.forEach((field) => visible.add(field));
                }

                document.querySelectorAll('[data-homepage-section-field]').forEach((wrapper) => {
                    wrapper.classList.toggle('hidden', !visible.has(wrapper.dataset.homepageSectionField));
                });

                const manualProducts = sourceType?.value === 'manual_products';
                productsPanel?.classList.toggle('hidden', !manualProducts || !['product_grid', 'product_carousel', 'mixed_showcase', 'featured_category'].includes(type));

                if (help) help.textContent = helpText[type] || 'Choose the settings for this homepage section.';
            };

            sectionType.addEventListener('change', showFields);
            sourceType?.addEventListener('change', showFields);
            showFields();
        })();

        document.getElementById('content-form')?.addEventListener('submit', () => {
            const button = document.getElementById('content-submit');
            if (!button) return;

            button.disabled = true;
            button.textContent = 'Saving...';
        });
    </script>
@endsection
