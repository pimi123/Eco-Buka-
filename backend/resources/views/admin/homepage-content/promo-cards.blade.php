@extends('admin.layout', ['title' => 'Edit Promo Card Section'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.homepage-content.index') }}">Back to Homepage Content Display</a>
            <h1 class="mt-2 text-2xl font-black">Edit Promo Card Section</h1>
            <p class="mt-2 max-w-4xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
                This screen controls one homepage promo section from one place. Choose whether it displays as one wide banner, two cards, a grid, or a carousel, then manage the cards shown inside it.
            </p>
        </div>
    </div>

    <form class="mt-5 grid gap-5" method="post" enctype="multipart/form-data" action="{{ route('admin.homepage-content.promo-cards.update', $section) }}">
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
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="text" name="title" value="{{ old('title', $section->title) }}" placeholder="New Products">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Display Limit</span>
                    <input class="rounded-md border border-slate-300 px-3 py-2" type="number" name="display_limit" value="{{ old('display_limit', $section->display_limit ?: 6) }}" min="1" max="24">
                </label>

                <label class="grid gap-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Visual Layout</span>
                    <select class="rounded-md border border-slate-300 px-3 py-2" name="layout_variant">
                        @foreach ([
                            'single_banner' => 'Single wide banner',
                            'two_cards' => 'Two equal cards',
                            'grid' => 'Responsive card grid',
                            'carousel' => 'Horizontal carousel',
                        ] as $optionValue => $label)
                            <option value="{{ $optionValue }}" @selected(old('layout_variant', $section->layout_variant ?: 'two_cards') === $optionValue)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="text-xs font-semibold text-slate-500">Use single banner for one strong promotion, two cards for paired campaigns, grid/carousel when there are more items.</span>
                </label>

                <label class="grid gap-2 lg:col-span-2">
                    <span class="text-xs font-bold uppercase text-slate-500">Section Subtitle</span>
                    <textarea class="min-h-24 rounded-md border border-slate-300 px-3 py-2" name="subtitle" placeholder="Fresh energy launches, seasonal offers, and smart power picks.">{{ old('subtitle', $section->subtitle) }}</textarea>
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
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-slate-500">Cards</p>
                    <h2 class="mt-1 text-xl font-black">Promo Cards</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Cards redirect users to products, categories, collections, or custom pages.</p>
                </div>
                <button class="rounded-md border border-slate-300 px-4 py-3 text-sm font-bold" type="button" data-add-promo-card>Add Card</button>
            </div>

            <div class="grid gap-4" data-promo-card-list>
                @foreach ($cards as $card)
                    @include('admin.homepage-content.partials.promo-card-form', ['card' => $card, 'index' => $loop->index, 'isTemplate' => false])
                @endforeach
            </div>

            <p class="rounded-lg border border-amber-100 bg-amber-50 p-3 text-sm font-semibold leading-6 text-amber-900">
                Tip: leave the button link empty only if this card should go to the general products page. Use paths like /category/power-stations, /collections/home-backup, or /products/stream-ultra-x.
            </p>
        </section>

        <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/95 p-4 backdrop-blur sm:-mx-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a class="rounded-md border border-slate-300 px-5 py-3 text-center text-sm font-bold" href="{{ route('admin.homepage-content.index') }}">Cancel</a>
                <button class="rounded-md bg-slate-950 px-5 py-3 text-sm font-bold text-white" type="submit">Save Section and Cards</button>
            </div>
        </div>
    </form>

    <template data-promo-card-template>
        @include('admin.homepage-content.partials.promo-card-form', ['card' => null, 'index' => '__INDEX__', 'isTemplate' => true])
    </template>

    <script>
        (() => {
            const list = document.querySelector('[data-promo-card-list]');
            const template = document.querySelector('[data-promo-card-template]');
            const addButton = document.querySelector('[data-add-promo-card]');
            let nextIndex = {{ $cards->count() }};

            addButton?.addEventListener('click', () => {
                if (!list || !template) return;

                const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                const card = wrapper.firstElementChild;
                if (!card) return;

                list.append(card);
                nextIndex += 1;
            });

            list?.addEventListener('click', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) return;

                const toggleButton = target.closest('[data-toggle-promo-card], [data-toggle-promo-card-button]');
                if (toggleButton) {
                    const card = toggleButton.closest('[data-promo-card]');
                    const body = card?.querySelector('[data-promo-card-body]');
                    const icon = card?.querySelector('[data-promo-card-icon]');
                    const headerButton = card?.querySelector('[data-toggle-promo-card]');
                    if (!body) return;

                    const shouldOpen = body.classList.contains('hidden');
                    body.classList.toggle('hidden', !shouldOpen);
                    if (icon) icon.textContent = shouldOpen ? '-' : '+';
                    headerButton?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                    return;
                }

                const removeButton = target.closest('[data-remove-promo-card]');
                if (!removeButton) return;

                const card = removeButton.closest('[data-promo-card]');
                if (!card) return;

                const deleteInput = card.querySelector('[data-delete-input]');
                const isExisting = card.querySelector('input[name$="[id]"]')?.value;

                if (isExisting) {
                    if (!window.confirm('Remove this promo card when you save?')) return;
                    if (deleteInput) deleteInput.value = '1';
                    card.classList.add('hidden');
                    return;
                }

                card.remove();
            });
        })();
    </script>
@endsection
