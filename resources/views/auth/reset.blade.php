@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
<h2 class="text-2xl font-bold">Choose a new password</h2>
<form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">@csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div><label class="label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required class="input">
        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <div><label class="label" for="password">New password</label><input id="password" name="password" type="password" required class="input">
        @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <div><label class="label" for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required class="input"></div>
    <button class="btn-primary w-full">Reset password</button>
</form>
@endsection
