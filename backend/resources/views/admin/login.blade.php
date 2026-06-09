@extends('admin.layout', ['title' => 'Admin Login'])

@section('content')
    <div class="mx-auto mt-12 max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-black">Admin Login</h1>
        <form class="mt-6 grid gap-4" method="post" action="{{ route('admin.login.post') }}">
            @csrf
            <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Email</span>
                <input class="rounded-md border border-slate-300 px-3 py-2" type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Password</span>
                <input class="rounded-md border border-slate-300 px-3 py-2" type="password" name="password" required>
            </label>
            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button class="rounded-md bg-slate-950 px-4 py-3 text-sm font-bold text-white">Login</button>
        </form>
    </div>
@endsection
