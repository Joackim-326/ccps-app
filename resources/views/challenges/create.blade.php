@extends('layouts.app')
@section('title', 'Register challenge')
@section('heading', 'Register client challenge')
@section('content')
<form method="POST" action="{{ route('challenges.store') }}" class="card mx-auto max-w-3xl space-y-6 p-6 sm:p-8"
>@csrf
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label class="label" for="index_number">Client's index number (registration number)</label>
            <input id="index_number" name="index_number" value="{{ old('index_number') }}" required class="input" placeholder="e.g. NS/..../..../....">
        </div>
        <div>
            <label class="label" for="category">Challenge category</label>
            <select id="category" name="category" required class="input">
                <option value="">Select category…</option>
                @foreach ($categories as $c)<option value="{{ $c }}" @selected(old('category') === $c)>{{ $c }}</option>@endforeach
            </select>
        </div>
    </div>
    <div id="otherBox" class="{{ old('category') === 'Other' ? '' : 'hidden' }}">
        <label class="label" for="other_category">Specify the challenge</label>
        <input id="other_category" name="other_category" value="{{ old('other_category') }}" class="input" placeholder="Briefly name the challenge">
    </div>
    <div>
        <label class="label" for="description">Challenge description</label>
        <textarea id="description" name="description" rows="5" required class="input" placeholder="Describe the client's problem in detail…">{{ old('description') }}</textarea>
    </div>
    <div>
        <label class="label" for="assigned_to">Submit to</label>
        <select id="assigned_to" name="assigned_to" required class="input">
            <option value="">Choose an officer or creator…</option>
            @foreach ($assignees->groupBy('role') as $role => $group)
                <optgroup label="{{ ucfirst($role) }}s">
                    @foreach ($group as $a)<option value="{{ $a->id }}" @selected(old('assigned_to') == $a->id)>{{ $a->name }}</option>@endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
    <div class="flex justify-end gap-3">
        <a href="{{ route('dashboard') }}" class="btn-ghost">Cancel</a>
        <button class="btn-primary">Submit challenge</button>
    </div>
</form>
<script>
    document.getElementById('category').addEventListener('change', e => {
        document.getElementById('otherBox').classList.toggle('hidden', e.target.value !== 'Other');
    });
</script>
@endsection
