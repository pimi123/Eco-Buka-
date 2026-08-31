@extends('admin.layout', ['title' => 'Edit Video Banner Section'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.homepage-content.index') }}">Back to Homepage Content Display</a>
            <h1 class="mt-2 text-2xl font-black">Edit Video Banner</h1>
            <p class="mt-2 max-w-4xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
                This controls one wide homepage banner. Use one active banner only; image, video, copy, CTA, order, and visibility are managed here.
            </p>
        </div>
    </div>

    <form class="mt-5 grid gap-5" method="post" enctype="multipart/form-data" action="{{ route('admin.homepage-content.video-banner.update', $section) }}">
        @csrf
        @method('put')

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Homepage Section</p>
                <h2 class="mt-1 text-xl font-black">{{ $section->title ?: str($section->section_key)->headline() }}</h2>
                <p class="mt-1 text-sm font-semibold text-slate-500">Internal key: {{ $section->section_key }}</p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Admin Section Name</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="section_title" value="{{ old('section_title', $section->title) }}" placeholder="Featured Video Promo">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Homepage Position</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order) }}" min="0">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Admin Description</span>
                    <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="section_subtitle" placeholder="Short internal note shown in the homepage content list.">{{ old('section_subtitle', $section->subtitle) }}</textarea>
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
                <p class="text-xs font-black uppercase text-slate-500">Banner Copy</p>
                <h2 class="mt-1 text-xl font-black">Text and CTA</h2>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Heading Above Banner</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="section_heading" value="{{ old('section_heading', $banner?->section_heading) }}" placeholder="Eco Buka Home Backup and Solar Storage">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Promo Label</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="eyebrow" value="{{ old('eyebrow', $banner?->eyebrow) }}" placeholder="Home Energy Solution">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Main Heading</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="title" value="{{ old('title', $banner?->title) }}" placeholder="Eco Buka Home Backup and Solar Storage">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Subtitle</span>
                    <textarea class="min-h-24 rounded-md border border-slate-300 px-3 py-2" name="subtitle" placeholder="Short readable text over the banner.">{{ old('subtitle', $banner?->subtitle) }}</textarea>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Price / Offer Text</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="price_text" value="{{ old('price_text', $banner?->price_text) }}" placeholder="Configured from EUR 1499">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Text</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="button_text" value="{{ old('button_text', $banner?->button_text ?: 'Buy Now') }}" placeholder="Buy Now">
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Button Link / Redirect</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="button_link" value="{{ old('button_link', $banner?->button_link) }}" placeholder="/category/home-battery">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Text Color</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="text_color">
                        @foreach (['light' => 'Light text', 'dark' => 'Dark text'] as $optionValue => $label)
                            <option value="{{ $optionValue }}" @selected(old('text_color', $banner?->text_color ?: 'light') === $optionValue)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Text Alignment</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="text_alignment">
                        @foreach (['left' => 'Left', 'center' => 'Center', 'right' => 'Right'] as $optionValue => $label)
                            <option value="{{ $optionValue }}" @selected(old('text_alignment', $banner?->text_alignment ?: 'left') === $optionValue)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="text-xs font-black uppercase text-slate-500">Banner Media</p>
                <h2 class="mt-1 text-xl font-black">Image and Video</h2>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Fallback / Desktop Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="background_image" accept="image/*">
                    @if ($banner?->background_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $banner->background_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="background_image_remove" value="1">
                            Remove image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
                    <span class="text-xs font-bold uppercase text-slate-500">Mobile Image</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="mobile_background_image" accept="image/*">
                    @if ($banner?->mobile_background_image_url)
                        <img class="h-28 w-52 rounded-md bg-slate-100 object-cover" src="{{ $banner->mobile_background_image_url }}" alt="">
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="mobile_background_image_remove" value="1">
                            Remove mobile image
                        </label>
                    @endif
                </section>

                <section class="grid gap-2 rounded-lg border border-slate-200 p-3 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Optional Background Video</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="background_video" accept="video/mp4,video/webm,video/ogg">
                    @if ($banner?->background_video_url)
                        <video class="h-36 w-full max-w-md rounded-md bg-slate-950 object-cover" src="{{ $banner->background_video_url }}" controls muted></video>
                        <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                            <input type="checkbox" name="background_video_remove" value="1">
                            Remove video
                        </label>
                    @endif
                    <p class="text-xs font-semibold text-slate-500">Upload optimized MP4/WebM. The frontend autoplays muted, loops, and uses the image as fallback.</p>
                </section>
            </div>
        </section>

        <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/95 p-4 backdrop-blur sm:-mx-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.homepage-content.index') }}">Cancel</a>
                <button class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Save Video Banner</button>
            </div>
        </div>
    </form>
@endsection
