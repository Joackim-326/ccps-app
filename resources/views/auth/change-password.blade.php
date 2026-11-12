@extends('layouts.app')
@section('title', 'Change password')
@section('heading', 'Change password')
@section('content')
<form method="POST" action="{{ route('password.change.update') }}" class="card max-w-lg space-y-5 p-6">@csrf @method('PUT')
    <div><label class="label" for="current_password">Current password</label><input id="current_password" name="current_password" type="password" required class="input"></div>
    <div><label class="label" for="password">New password</label><input id="password" name="password" type="password" required class="input"><p class="mt-1 text-xs text-slate-500">At least 8 characters.</p></div>
    <div><label class="label" for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required class="input"></div>
    <button class="btn-primary">Update password</button>
</form>
@endsection
