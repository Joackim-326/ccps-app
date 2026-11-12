@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('content')
@php $u = auth()->user(); @endphp
<div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-brand-600 to-violet-600 p-6 text-white shadow-lg sm:p-8">
    <p class="text-sm text-indigo-100">{{ now()->format('l, d F Y') }}</p>
    <h2 class="mt-1 text-2xl font-extrabold sm:text-3xl">Hello, {{ $u->first_name }} 👋</h2>
        @if ($u->isReceptionist())
        <a href="{{ route('challenges.create') }}" class="mt-5 inline-flex rounded-xl bg-white px-4 py-2 text-sm font-semibold text-brand-700 shadow hover:bg-indigo-50">+ Register a challenge</a>
    @endif
</div>

@php
    $cards = [
        [$u->isReceptionist() ? 'Pending challenges' : 'In progress', $stats['open'], 'bg-amber-100 text-amber-700', route('challenges.index')],
        ['Attended', $stats['attended'], 'bg-emerald-100 text-emerald-700', route('challenges.index', ['status' => 'attended'])],
        ['Unread messages', $stats['unread'], 'bg-sky-100 text-sky-700', route('chat.index')],
    ];
    if ($u->isCreator()) {
        $cards[] = ['All open (system)', $stats['all_open'], 'bg-violet-100 text-violet-700', route('admin.challenges.index', ['status' => 'in_progress'])];
        $cards[] = ['All attended (system)', $stats['all_attended'], 'bg-teal-100 text-teal-700', route('admin.challenges.index', ['status' => 'attended'])];
        $cards[] = ['Users', $stats['users'], 'bg-rose-100 text-rose-700', route('admin.users.index')];
    }
@endphp
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($cards as [$label, $value, $color, $url])
        <a href="{{ $url }}" class="card group p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">{{ $label }}</span>
                <span class="badge {{ $color }}">&nbsp;</span>
            </div>
            <div class="mt-3 text-4xl font-extrabold text-slate-900">{{ $value }}</div>
        </a>
    @endforeach
</div>

<div class="card mt-8">
    <div class="border-b border-slate-100 px-5 py-4 font-semibold">Recent challenges</div>
    @include('challenges.table', ['challenges' => $recent, 'assignees' => collect(), 'compact' => true])
</div>
@endsection
