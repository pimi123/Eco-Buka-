@php
    $cardTitle = old("cards.$index.title", $card?->title);
    $backgroundUrl = $card?->background_image_url;
    $mobileBackgroundUrl = $card?->mobile_background_image_url;
    $videoUrl = $card?->background_video_url;
    $defaultSortOrder = is_numeric($index) ? ((int) $index + 1) : 0;
    $sortOrder = old("cards.$index.sort_order", $card?->sort_order ?? $defaultSortOrder);
    $isActive = (bool) old("cards.$index.active", $card?->active ?? true);
@endphp

<article class="overflow-hidden rounded-lg border border-slate-200 bg-white" data-promo-card>
    <input type="hidden" name="cards[{{ $index }}][id]" value="{{ $card?->id }}">
    <input type="hidden" name="cards[{{ $index }}][_delete]" value="0" data-delete-input>

    <div class="flex flex-col gap-3 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <button class="group flex min-w-0 flex-1 items-start gap-3 text-left" type="button" data-toggle-promo-card aria-expanded="{{ $card ? 'false' : 'true' }}">
            <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-sm font-black text-slate-700 shadow-sm" data-promo-card-icon>{{ $card ? '+' : '-' }}</span>
            <span class="min-w-0">
                <span class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-black uppercase text-slate-500">Promo Card</span>
                    <span @class([
                        'rounded-full px-2 py-0.5 text-[11px] font-black uppercase',
                        'bg-emerald-100 text-emerald-800' => $isActive,
                        'bg-slate-200 text-slate-600' => ! $isActive,
                    ])>{{ $isActive ? 'Active' : 'Inactive' }}</span>
                    <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-black uppercase text-slate-500">Position {{ $sortOrder }}</span>
                </span>
                <span class="mt-1 block truncate text-lg font-black text-slate-950" data-card-title-preview>{{ $cardTitle ?: 'New promo card' }}</span>
                <span class="mt-1 block truncate text-sm font-semibold text-slate-500">{{ old("cards.$index.button_link", $card?->button_link) ?: 'No redirect set' }}</span>
            </span>
        </button>

        <div class="flex shrink-0 flex-wrap gap-2">
            <button class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold" type="button" data-toggle-promo-card-button>Edit details</button>
            <button class="rounded-md border border-red-200 bg-white px-3 py-2 text-sm font-bold text-red-700" type="button" data-remove-promo-card>Remove</button>
        </div>
    </div>

    <div class="{{ $card ? 'hidden' : '' }} grid gap-4 border-t border-slate-200 p-4 lg:grid-cols-2" data-promo-card-body>
        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Label</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="cards[{{ $index }}][label]" value="{{ old("cards.$index.label", $card?->label) }}" placeholder="New / Summer Sale">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Title</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="cards[{{ $index }}][title]" value="{{ $cardTitle }}" placeholder="Summer Sale">
        </label>

        <label class="grid gap-2 lg:col-span-2">
            <span class="text-xs font-bold uppercase text-slate-500">Subtitle</span>
            <textarea class="min-h-20 rounded-md border border-slate-300 px-3 py-2" name="cards[{{ $index }}][subtitle]" placeholder="Short text displayed inside the card.">{{ old("cards.$index.subtitle", $card?->subtitle) }}</textarea>
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Button Text</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="cards[{{ $index }}][button_text]" value="{{ old("cards.$index.button_text", $card?->button_text) }}" placeholder="Shop Now">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Button Link / Redirect</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="cards[{{ $index }}][button_link]" value="{{ old("cards.$index.button_link", $card?->button_link) }}" placeholder="/category/power-stations">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Fallback Category Slug</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="cards[{{ $index }}][category_slug]" value="{{ old("cards.$index.category_slug", $card?->category_slug) }}" placeholder="power-stations">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Text Color</span>
            <select class="rounded-md border border-slate-300 px-3 py-2" name="cards[{{ $index }}][text_color]">
                <option value="light" @selected(old("cards.$index.text_color", $card?->text_color ?: 'light') === 'light')>Light text</option>
                <option value="dark" @selected(old("cards.$index.text_color", $card?->text_color) === 'dark')>Dark text</option>
            </select>
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Card Position</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="cards[{{ $index }}][sort_order]" value="{{ $sortOrder }}" min="0">
        </label>

        <label class="flex items-center gap-2 self-end text-sm font-bold">
            <input type="hidden" name="cards[{{ $index }}][active]" value="0">
            <input type="checkbox" name="cards[{{ $index }}][active]" value="1" @checked(old("cards.$index.active", $card?->active ?? true))>
            Active card
        </label>

        <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
            <span class="text-xs font-bold uppercase text-slate-500">Desktop Background Image</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="cards[{{ $index }}][background_image]" accept="image/*">
            @if ($backgroundUrl)
                <img class="h-24 w-36 rounded-md bg-slate-100 object-cover" src="{{ $backgroundUrl }}" alt="">
                <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input type="checkbox" name="cards[{{ $index }}][background_image_remove]" value="1">
                    Remove desktop image
                </label>
            @endif
        </section>

        <section class="grid gap-2 rounded-lg border border-slate-200 p-3">
            <span class="text-xs font-bold uppercase text-slate-500">Mobile Background Image</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="cards[{{ $index }}][mobile_background_image]" accept="image/*">
            @if ($mobileBackgroundUrl)
                <img class="h-24 w-36 rounded-md bg-slate-100 object-cover" src="{{ $mobileBackgroundUrl }}" alt="">
                <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input type="checkbox" name="cards[{{ $index }}][mobile_background_image_remove]" value="1">
                    Remove mobile image
                </label>
            @endif
        </section>

        <section class="grid gap-2 rounded-lg border border-slate-200 p-3 lg:col-span-2">
            <span class="text-xs font-bold uppercase text-slate-500">Optional Background Video</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="cards[{{ $index }}][background_video]" accept="video/mp4,video/webm,video/ogg">
            @if ($videoUrl)
                <video class="h-32 w-full max-w-sm rounded-md bg-slate-950 object-cover" src="{{ $videoUrl }}" muted controls></video>
                <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input type="checkbox" name="cards[{{ $index }}][background_video_remove]" value="1">
                    Remove video
                </label>
            @endif
            <span class="text-xs font-semibold text-slate-500">If uploaded, the public card can use this as a muted looping background with the image as fallback.</span>
        </section>
    </div>
</article>
