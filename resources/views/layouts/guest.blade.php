<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') · {{ config('app.name', 'CCPS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-gradient-to-br from-brand-900 via-brand-700 to-violet-600 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
        <div class="relative flex items-center gap-3 text-lg font-bold">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/20 ring-1 ring-white/30">C</span> CCPS
        </div>
        <div class="relative">
            <h1 class="text-4xl font-extrabold leading-tight">Customer Care<br>Portal System</h1>
            <p class="mt-4 max-w-md text-indigo-100">Welcome to Customer Care Portal System. Register client's challenges and keep every step on record.</p>
        </div>
        <p class="relative text-sm text-indigo-200">&copy; {{ date('Y') }} Customer Care Portal System</p>
    </div>
    <main class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 font-bold text-white">C</span>
                <span class="text-lg font-bold">CCPS</span>
            </div>
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
