@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<h2 class="text-2xl font-bold">Welcome back</h2>
<p class="mt-1 text-sm text-slate-500">Sign in to your account to continue.</p>
<form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">@csrf
    <div>
        <label class="label" for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="password">Password</label>
        <input id="password" name="password" type="password" required class="input">
    </div>
    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Remember me</label>
        <a href="{{ route('password.request') }}" class="font-medium text-brand-600 hover:underline">Forgot password?</a>
    </div>
    <button class="btn-primary w-full">Sign in</button>
</form>
<p class="mt-6 text-center text-sm text-slate-500">New here? <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:underline">Create an account</a></p>
@endsection
