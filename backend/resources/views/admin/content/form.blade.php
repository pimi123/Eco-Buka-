@extends('admin.layout', ['title' => ($item->exists ? 'Edit ' : 'Add ').$config['label']])

@section('content')
    <h1 class="text-2xl font-black">{{ $item->exists ? 'Edit' : 'Add' }} {{ $config['label'] }}</h1>
    <form class="mt-5 grid gap-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.content.update', [$resource, $item]) : route('admin.content.store', $resource) }}">
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
                            <span class="text-xs font-semibold text-slate-500">Use one spec per line, for example: Capacity: 2kWh</span>
                        @endif
                    </label>
                @elseif ($type === 'image')
                    @php($previewUrl = $item->{$field.'_url'} ?? ($item->{$field} ? Storage::disk('public')->url($item->{$field}) : null))
                    <label class="grid gap-2">
                        <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="{{ $field }}" accept="image/*">
                        @if ($previewUrl)
                            <img class="h-24 w-32 rounded-md object-cover" src="{{ $previewUrl }}" alt="">
                        @endif
                    </label>
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
                    <label class="grid gap-2 md:col-span-2">
                        <span class="text-xs font-bold uppercase text-slate-500">{{ str($field)->headline() }}</span>
                        <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="{{ $field }}[]" accept="image/*" multiple>
                    </label>
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
            <button class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white">Save {{ $config['label'] }}</button>
            <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.content.index', $resource) }}">Back</a>
        </div>
    </form>
@endsection
