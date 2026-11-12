@extends('layouts.app')
@section('title', 'Manage users')
@section('heading', 'Manage users')
@section('content')
<div class="card">
    <form method="GET" class="flex flex-wrap gap-2 border-b border-slate-100 p-4">
        <input name="q" value="{{ request('q') }}" placeholder="Search name, email or contact…" class="input !w-64">
        <select name="role" class="input !w-44"><option value="">All roles</option>
            @foreach (\App\Models\User::ROLES as $r)<option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>@endforeach
        </select>
        <button class="btn-ghost">Filter</button>
    </form>
    <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/70"><tr><th class="th">User</th><th class="th">Contact</th><th class="th">Role</th><th class="th">Joined</th><th class="th text-right">Actions</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
        @foreach ($users as $user)
            <tr>
                <td class="td"><div class="font-semibold text-slate-900">{{ $user->name }}</div><div class="text-xs text-slate-500">{{ $user->email }}</div></td>
                <td class="td">{{ $user->contact }}@if ($user->alt_contact)<div class="text-xs text-slate-500">{{ $user->alt_contact }}</div>@endif</td>
                <td class="td"><span class="badge capitalize {{ ['creator'=>'bg-amber-100 text-amber-800','officer'=>'bg-sky-100 text-sky-800','receptionist'=>'bg-emerald-100 text-emerald-800'][$user->role] }}">{{ $user->role }}</span></td>
                <td class="td text-xs text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                <td class="td text-right">
                    <button type="button" data-toggle="edit{{ $user->id }}" class="btn-ghost btn-sm">Edit</button>
                    @if ($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete {{ $user->name }}? Their messages will be removed.')">@csrf @method('DELETE')
                        <button class="btn-danger btn-sm">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            <tr id="edit{{ $user->id }}" class="{{ $errors->hasBag('user'.$user->id) ? '' : 'hidden' }} bg-slate-50">
                <td colspan="5" class="px-4 py-4">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@csrf @method('PUT')
                        <div><label class="label">First name</label><input name="first_name" value="{{ $user->first_name }}" required class="input"></div>
                        <div><label class="label">Middle name</label><input name="middle_name" value="{{ $user->middle_name }}" required class="input"></div>
                        <div><label class="label">Email</label><input type="email" name="email" value="{{ $user->email }}" required class="input"></div>
                        <div><label class="label">Role</label>
                            <select name="role" class="input">@foreach (\App\Models\User::ROLES as $r)<option value="{{ $r }}" @selected($user->role === $r)>{{ ucfirst($r) }}</option>@endforeach</select>
                            @if ($errors->hasBag('user'.$user->id))<p class="mt-1 text-sm text-rose-600">{{ $errors->getBag('user'.$user->id)->first('role') }}</p>@endif
                        </div>
                        <div><label class="label">Contact</label><input name="contact" value="{{ $user->contact }}" required class="input"></div>
                        <div><label class="label">Alternative contact</label><input name="alt_contact" value="{{ $user->alt_contact }}" class="input"></div>
                        <div><label class="label">New password <span class="text-slate-400">(optional)</span></label><input type="password" name="password" autocomplete="new-password" class="input"></div>
                        <div class="flex items-end gap-2"><button class="btn-primary">Save changes</button></div>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @if ($users->hasPages())<div class="border-t border-slate-100 p-4">{{ $users->links() }}</div>@endif
</div>
@endsection
