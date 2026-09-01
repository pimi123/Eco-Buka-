@extends('admin.layout', ['title' => 'Contact Messages'])

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black">Contact Messages</h1>
            <p class="mt-1 text-sm text-slate-600">Offer, return, support, and general website messages saved from the contact form.</p>
        </div>
    </div>

    <form class="mt-5 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto]" method="get">
        <input class="rounded-md border border-slate-300 px-3 py-2" type="search" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email, message">
        <select class="rounded-md border border-slate-300 px-3 py-2" name="purpose">
            <option value="">All purposes</option>
            @foreach ($purposes as $purpose)
                <option value="{{ $purpose }}" @selected(request('purpose') === $purpose)>{{ str($purpose)->headline() }}</option>
            @endforeach
        </select>
        <select class="rounded-md border border-slate-300 px-3 py-2" name="status">
            <option value="">All statuses</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="mt-5 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Purpose</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Message</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($messages as $message)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="block font-black">{{ $message->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $message->phone }}</span>
                                <span class="block text-xs text-slate-500">{{ $message->email ?: '-' }}</span>
                            </td>
                            <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ str($message->purpose)->headline() }}</span></td>
                            <td class="px-4 py-3 font-semibold">{{ $message->subject ?: '-' }}</td>
                            <td class="max-w-xs px-4 py-3 text-slate-600">{{ str($message->message)->limit(90) }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold text-white">{{ str($message->status)->headline() }}</span></td>
                            <td class="px-4 py-3">{{ $message->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3"><a class="rounded-md border border-slate-300 px-3 py-2 font-semibold" href="{{ route('admin.contact-messages.show', $message) }}">Open</a></td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-8 text-center text-slate-500" colspan="7">No contact messages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
