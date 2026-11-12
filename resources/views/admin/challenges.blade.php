@extends('layouts.app')
@section('title', 'All challenges')
@section('heading', 'All challenges')
@section('content')
<div class="card">
    <form method="GET" class="flex flex-wrap gap-2 border-b border-slate-100 p-4">
        <input name="q" value="{{ request('q') }}" placeholder="Search index, reference, description…" class="input !w-72">
        <select name="status" class="input !w-44">
            <option value="">All statuses</option>
            <option value="in_progress" @selected(request('status') === 'in_progress')>In progress</option>
            <option value="attended" @selected(request('status') === 'attended')>Attended</option>
        </select>
        <select name="assignee" class="input !w-52"><option value="">Submitted to: anyone</option>
            @foreach ($assignees as $a)<option value="{{ $a->id }}" @selected(request('assignee') == $a->id)>{{ $a->name }}</option>@endforeach
        </select>
        <button class="btn-ghost">Filter</button>
    </form>
    @include('challenges.table', ['challenges' => $challenges, 'assignees' => $assignees, 'admin' => true])
    @if ($challenges->hasPages())<div class="border-t border-slate-100 p-4">{{ $challenges->links() }}</div>@endif
</div>
@endsection
