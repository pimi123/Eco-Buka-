@extends('admin.layout', ['title' => ($item->exists ? 'Edit ' : 'Add ').$config['label']])

@section('content')
    <h1 class="text-2xl font-black">{{ $item->exists ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
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
                @endphp
                @if ($type === 'boolean')
                    <label class="flex items-center gap-2 text-sm font-semibold">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $item->exists ? $item->{$field} : true))>
                        {{ str($field)->headline() }}
                    </label>
                @elseif ($type === 'textarea' || $type === 'json')
                    <label class="grid gap-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                        <textarea class="min-h-28 rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">{{ $value }}</textarea>
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
                            <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
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
                        <span class="text-xs font-bold uppercase text-slate-500">Category</span>
                        <select class="rounded-md border border-slate-300 px-3 py-2" name="{{ $field }}">
                            <option value="">No category</option>
                            @foreach ($options['categories'] as $id => $name)
                                <option value="{{ $id }}" @selected((string) $value === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                @else
                    <label class="grid gap-2">
                        <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="{{ in_array($type, ['price', 'number']) ? 'number' : 'text' }}" step="{{ $type === 'price' ? '0.01' : '1' }}" name="{{ $field }}" value="{{ $value }}">
                    </label>
                @endif
            @endforeach
        </div>

        @if ($resource === 'showcase-sections')
            <section class="rounded-lg border border-slate-200 p-4">
                <h2 class="font-black">Products in this section</h2>
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

        document.getElementById('content-form')?.addEventListener('submit', () => {
            const button = document.getElementById('content-submit');
            if (!button) return;

            button.disabled = true;
            button.textContent = 'Saving...';
        });
    </script>
@endsection
