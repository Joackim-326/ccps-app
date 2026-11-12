@php
    $u = auth()->user();
    $roleColor = ['creator' => 'bg-amber-400/90 text-amber-950', 'officer' => 'bg-sky-300 text-sky-950', 'receptionist' => 'bg-emerald-300 text-emerald-950'][$u->role];
    $links = [];
    $links[] = ['Dashboard', route('dashboard'), 'dashboard', 'M3 12l9-9 9 9M5 10v10h14V10'];
    if ($u->isReceptionist()) {
        $links[] = ['Register challenge', route('challenges.create'), 'challenges.create', 'M12 5v14M5 12h14'];
        $links[] = ['Pending challenges', route('challenges.index'), 'challenges.index:in_progress', 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
        $links[] = ['Attended challenges', route('challenges.index', ['status' => 'attended']), 'challenges.index:attended', 'M5 13l4 4L19 7'];
    } else {
        $links[] = ['In progress', route('challenges.index'), 'challenges.index:in_progress', 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
        $links[] = ['Attended', route('challenges.index', ['status' => 'attended']), 'challenges.index:attended', 'M5 13l4 4L19 7'];
    }
    $links[] = ['Chat', route('chat.index'), 'chat.*', 'M8 10h8M8 14h5M21 12c0 4.4-4 8-9 8a10 10 0 01-3.5-.6L3 21l1.6-4.2A7.6 7.6 0 013 12c0-4.4 4-8 9-8s9 3.6 9 8z'];
    if ($u->isCreator()) {
        $admin = [
            ['Manage users', route('admin.users.index'), 'admin.users.*', 'M17 20h5v-2a4 4 0 00-5-3.9M9 11a4 4 0 100-8 4 4 0 000 8zM2 20v-2a4 4 0 014-4h6a4 4 0 014 4v2'],
            ['All challenges', route('admin.challenges.index'), 'admin.challenges.*', 'M9 5h6M9 9h6M9 13h4M5 3h14v18H5z'],
            ['System logs', route('admin.logs.index'), 'admin.logs.*', 'M4 6h16M4 12h16M4 18h10'],
        ];
    }
    $status = request('status') === 'attended' ? 'attended' : 'in_progress';
    $isActive = function ($key) use ($status) {
        if (str_contains($key, ':')) { [$r, $s] = explode(':', $key); return request()->routeIs($r) && $status === $s; }
        return request()->routeIs($key);
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'CCPS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased">
<div class="flex min-h-screen">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col bg-gradient-to-b from-brand-900 to-slate-900 p-5 lg:flex">
        <div class="mb-8 flex items-center gap-3 px-2 text-white">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15 font-bold ring-1 ring-white/20">C</span>
            <div><div class="font-bold leading-none">CCPS</div><div class="text-xs text-indigo-200">Customer Care Portal</div></div>
        </div>
        <nav class="flex-1 space-y-1 overflow-y-auto">
            @foreach ($links as [$label, $url, $key, $icon])
                <a href="{{ $url }}" class="nav-link {{ $isActive($key) ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                    <span class="flex-1">{{ $label }}</span>
                    @if ($key === 'chat.*')
                        <span data-unread-badge data-url="{{ route('chat.unread') }}" class="rounded-full bg-rose-500 px-2 py-0.5 text-xs font-bold text-white {{ $u->unreadMessageCount() ? '' : 'hidden' }}">{{ $u->unreadMessageCount() }}</span>
                    @endif
                </a>
            @endforeach
            @isset($admin)
                <div class="px-3 pb-1 pt-6 text-xs font-semibold uppercase tracking-wider text-indigo-300">Administration</div>
                @foreach ($admin as [$label, $url, $key, $icon])
                    <a href="{{ $url }}" class="nav-link {{ $isActive($key) ? 'nav-link-active' : '' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>{{ $label }}
                    </a>
                @endforeach
            @endisset
            <div class="px-3 pb-1 pt-6 text-xs font-semibold uppercase tracking-wider text-indigo-300">Account</div>
            <a href="{{ route('password.change') }}" class="nav-link {{ request()->routeIs('password.change') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 15v2M6 11h12v9H6zM8 11V8a4 4 0 118 0v3"/></svg>Change password
            </a>
        </nav>
        <div class="mt-4 rounded-2xl bg-white/10 p-3 text-white">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-white/20 text-sm font-bold">{{ $u->initials }}</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold">{{ $u->name }}</div>
                    <span class="badge mt-0.5 {{ $roleColor }} capitalize"></span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
                <button class="w-full rounded-xl bg-white/10 py-2 text-sm font-medium hover:bg-white/20">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col lg:pl-72">
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-slate-200 bg-white/80 px-4 py-3 backdrop-blur lg:px-8">
            <button type="button" data-toggle="sidebar" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-bold">@yield('heading', 'Dashboard')</h1>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @if (session('status'))
                <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
