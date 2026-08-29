@extends('admin.layout', ['title' => $config['label'].'s'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-black">{{ $config['label'] }}s</h1>
        <a class="rounded-md bg-slate-950 px-4 py-3 text-center text-sm font-bold text-white" href="{{ route('admin.content.create', $resource) }}">Add {{ $config['label'] }}</a>
    </div>
    @if (!empty($config['description']))
        <p class="mt-3 max-w-3xl rounded-lg border border-sky-100 bg-sky-50 p-3 text-sm font-semibold leading-6 text-sky-900">
            {{ $config['description'] }}
        </p>
    @endif
    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Title / Name</th><th class="px-4 py-3">Key / Slug</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $item->name ?? $item->title ?? $item->section_heading ?? $item->section_key }}</td>
                            <td class="px-4 py-3">
                                @if ($resource === 'promo-cards')
                                    {{ $item->homepageSection?->title ?: $item->section_key ?: '-' }}
                                @else
                                    {{ $item->slug ?? $item->section_key ?? '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $item->sort_order }}</td>
                            <td class="px-4 py-3">{{ $item->active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a class="rounded-md border border-slate-300 px-3 py-2 font-semibold" href="{{ route('admin.content.edit', [$resource, $item]) }}">Edit</a>
                                    <form method="post" action="{{ route('admin.content.destroy', [$resource, $item]) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('delete')
                                        <button class="rounded-md border border-slate-300 px-3 py-2 font-semibold">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection
