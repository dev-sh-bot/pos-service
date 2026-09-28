@extends('layouts.auth')

@section('title', 'Forgot Password - ' . \App\Helpers\CommonHelper::getBrandName())

@section('content')
    <h2 class="snd-auth-title">Reset your password</h2>
    <p class="snd-auth-subtitle">Enter your email and we’ll send you a secure reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="email">Email address</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="mail" />
                <input id="email" type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email address" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>
            @error('email')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <button type="submit" class="snd-auth-submit">Request reset link</button>
    </form>

    <div class="snd-auth-links"><a href="{{ route('login') }}">Back to sign in</a>@if(Route::has('register'))<a href="{{ route('register') }}">Create account</a>@endif</div>
@endsection
