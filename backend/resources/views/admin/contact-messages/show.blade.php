@extends('admin.layout', ['title' => $contactMessage->name])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a class="text-sm font-bold text-slate-500 hover:text-slate-950" href="{{ route('admin.contact-messages.index') }}">← Back to contact messages</a>
            <h1 class="mt-2 text-2xl font-black">{{ $contactMessage->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Received {{ $contactMessage->created_at?->format('d M Y H:i') }}</p>
        </div>
        <span class="w-fit rounded-full bg-slate-950 px-4 py-2 text-sm font-bold text-white">{{ str($contactMessage->status)->headline() }}</span>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_360px]">
        <section class="grid gap-5">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Message details</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-slate-500">Purpose</dt><dd>{{ str($contactMessage->purpose)->headline() }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Subject</dt><dd>{{ $contactMessage->subject ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Phone</dt><dd><a class="font-semibold hover:underline" href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a></dd></div>
                    <div><dt class="font-bold text-slate-500">Email</dt><dd>{{ $contactMessage->email ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">Source page</dt><dd>{{ $contactMessage->source_path ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">Message</dt><dd class="mt-1 whitespace-pre-line rounded-md bg-slate-50 p-4 leading-6">{{ $contactMessage->message }}</dd></div>
                </dl>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Technical context</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-slate-500">IP address</dt><dd>{{ $contactMessage->ip_address ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Read at</dt><dd>{{ $contactMessage->read_at?->format('d M Y H:i') ?: '-' }}</dd></div>
                    <div><dt class="font-bold text-slate-500">Closed at</dt><dd>{{ $contactMessage->closed_at?->format('d M Y H:i') ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="font-bold text-slate-500">User agent</dt><dd class="break-words">{{ $contactMessage->user_agent ?: '-' }}</dd></div>
                </dl>
            </div>
        </section>

        <form class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm" method="post" action="{{ route('admin.contact-messages.update', $contactMessage) }}">
            @csrf
            @method('patch')
            <h2 class="text-lg font-black">Manage message</h2>
            <label class="mt-4 grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Status</span>
                <select class="rounded-md border border-slate-300 px-3 py-2" name="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($contactMessage->status === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-4 grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Admin note</span>
                <textarea class="min-h-36 rounded-md border border-slate-300 px-3 py-2" name="admin_note">{{ old('admin_note', $contactMessage->admin_note) }}</textarea>
            </label>
            <button class="mt-4 w-full rounded-md bg-slate-950 px-4 py-3 text-sm font-bold text-white">Save message</button>
        </form>
    </div>
@endsection
