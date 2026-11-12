@extends('layouts.app')
@php $u = auth()->user(); $title = $status === 'attended' ? 'Attended challenges' : ($u->isReceptionist() ? 'Pending challenges' : 'Challenges in progress'); @endphp
@section('title', $title)
@section('heading', $title)
@section('content')
<div class="card">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
        <div class="inline-flex rounded-xl bg-slate-100 p-1 text-sm font-medium">
            <a href="{{ route('challenges.index') }}" class="rounded-lg px-4 py-1.5 {{ $status === 'in_progress' ? 'bg-white shadow text-brand-700' : 'text-slate-500' }}">{{ $u->isReceptionist() ? 'Pending' : 'In progress' }}</a>
            <a href="{{ route('challenges.index', ['status' => 'attended']) }}" class="rounded-lg px-4 py-1.5 {{ $status === 'attended' ? 'bg-white shadow text-brand-700' : 'text-slate-500' }}">Attended</a>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input name="q" value="{{ request('q') }}" placeholder="Search index or reference…" class="input !w-64">
            <button class="btn-ghost">Search</button>
        </form>
    </div>
    @include('challenges.table', ['challenges' => $challenges, 'assignees' => $assignees])
    @if ($challenges->hasPages())<div class="border-t border-slate-100 p-4">{{ $challenges->links() }}</div>@endif
</div>
@endsection
