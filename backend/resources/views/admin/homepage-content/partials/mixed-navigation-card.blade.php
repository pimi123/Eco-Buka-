@php
    $cardId = old("cards.{$index}.id", $card?->id);
@endphp

<details class="rounded-lg border border-slate-200 bg-slate-50 p-4" @if ($card) open @endif>
    <summary class="cursor-pointer text-sm font-black">
        {{ $card?->title ?: 'New side card' }}
    </summary>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <input type="hidden" name="cards[{{ $index }}][id]" value="{{ $cardId }}">

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Card Title</span>
            <input class="rounded-md border border-slate-300 bg-white px-3 py-2" type="text" name="cards[{{ $index }}][title]" value="{{ old("cards.{$index}.title", $card?->title) }}">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Redirect Link</span>
            <input class="rounded-md border border-slate-300 bg-white px-3 py-2" type="text" name="cards[{{ $index }}][link]" value="{{ old("cards.{$index}.link", $card?->link) }}" placeholder="/collections/home-backup">
        </label>

        <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Sort Order</span>
            <input class="rounded-md border border-slate-300 bg-white px-3 py-2" type="number" name="cards[{{ $index }}][sort_order]" value="{{ old("cards.{$index}.sort_order", $card?->sort_order ?? $index + 1) }}" min="0">
        </label>

        <label class="flex items-center gap-2 self-end text-sm font-bold">
            <input type="hidden" name="cards[{{ $index }}][active]" value="0">
            <input type="checkbox" name="cards[{{ $index }}][active]" value="1" @checked(old("cards.{$index}.active", $card?->active ?? true))>
            Active
        </label>

        <section class="grid gap-2 rounded-lg border border-slate-200 bg-white p-3 lg:col-span-2">
            <span class="text-xs font-bold uppercase text-slate-500">Image</span>
            <input class="rounded-md border border-slate-300 px-3 py-2" type="file" name="cards[{{ $index }}][image]" accept="image/*">
            @if ($card?->image_url)
                <img class="h-24 w-40 rounded-md bg-slate-100 object-contain" src="{{ $card->image_url }}" alt="">
                <label class="flex items-center gap-2 text-sm font-semibold text-red-700">
                    <input type="checkbox" name="cards[{{ $index }}][image_remove]" value="1">
                    Remove image
                </label>
            @endif
        </section>

        @if ($card)
            <label class="flex items-center gap-2 text-sm font-bold text-red-700">
                <input type="checkbox" name="cards[{{ $index }}][_delete]" value="1">
                Delete this side card
            </label>
        @endif
    </div>
</details>
