@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<h2 class="text-2xl font-bold">Create your account</h2>
<p class="mt-1 text-sm text-slate-500">Accounts are created as receptionist by default. A creator can change your role later.</p>
<form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">@csrf
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ([['first_name','First name','text'],['middle_name','Middle name','text']] as [$n,$l,$t])
            <div><label class="label" for="{{ $n }}">{{ $l }}</label>
            <input id="{{ $n }}" name="{{ $n }}" type="{{ $t }}" value="{{ old($n) }}" required class="input">
            @error($n)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
        @endforeach
    </div>
    <div><label class="label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required class="input">
        @error('email')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label" for="contact">Contact</label><input id="contact" name="contact" value="{{ old('contact') }}" required class="input">
            @error('contact')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
        <div><label class="label" for="alt_contact">Alternative contact <span class="text-slate-400">(optional)</span></label><input id="alt_contact" name="alt_contact" value="{{ old('alt_contact') }}" class="input">
            @error('alt_contact')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    </div>
    <div><label class="label" for="password">Password</label><input id="password" name="password" type="password" required class="input">
        @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror</div>
    <div><label class="label" for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required class="input"></div>
    <button class="btn-primary w-full">Create account</button>
</form>
<p class="mt-6 text-center text-sm text-slate-500">Already registered? <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:underline">Sign in</a></p>
@endsection
