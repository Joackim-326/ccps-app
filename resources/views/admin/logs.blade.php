@extends('layouts.app')
@section('title', 'System logs')
@section('heading', 'System logs')
@section('content')
<div class="card">
    <form method="GET" class="flex flex-wrap gap-2 border-b border-slate-100 p-4">
        <input name="q" value="{{ request('q') }}" placeholder="Search description…" class="input !w-64">
        <select name="action" class="input !w-52"><option value="">All actions</option>
            @foreach ($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ str_replace('_', ' ', $a) }}</option>@endforeach
        </select>
        <button class="btn-ghost">Filter</button>
    </form>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/70"><tr><th class="th">When</th><th class="th">User</th><th class="th">Action</th><th class="th">Details</th><th class="th">IP</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($logs as $log)
            <tr>
                <td class="td whitespace-nowrap text-xs text-slate-500">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                <td class="td">{{ $log->user?->name ?? 'System / deleted user' }}@if ($log->user)<div class="text-xs capitalize text-slate-500">{{ $log->user->role }}</div>@endif</td>
                <td class="td"><span class="badge bg-slate-100 text-slate-700">{{ str_replace('_', ' ', $log->action) }}</span></td>
                <td class="td">{{ $log->description }}</td>
                <td class="td text-xs text-slate-500">{{ $log->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No log entries.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    @if ($logs->hasPages())<div class="border-t border-slate-100 p-4">{{ $logs->links() }}</div>@endif
</div>
@endsection
