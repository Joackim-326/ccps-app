@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
<h2 class="text-2xl font-bold">Forgot your password?</h2>
<p class="mt-1 text-sm text-slate-500">Enter your email and we'll send you a reset link.</p>
<form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">@csrf
    <div><label class="label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input">
        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <button class="btn-primary w-full">Email reset link</button>
</form>
<p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Back to sign in</a></p>
@endsection
