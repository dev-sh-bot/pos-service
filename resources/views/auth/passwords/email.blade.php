@extends('layouts.auth')

@section('title', 'Forgot Password - Knowhere POS')

@section('content')
    <span class="snd-auth-form-index">{{ __('Account recovery') }}</span>
    <h2 class="snd-auth-title">{{ __('Reset password') }}<span>.</span></h2>
    <p class="snd-auth-subtitle">{{ __('Enter your email and we’ll send you a secure reset link.') }}</p>

    <form method="POST" action="{{ route('password.email') }}" class="snd-auth-form">
        @csrf
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="email">Email address</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="mail" />
                <input id="email" type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email address" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>
            @error('email')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <button type="submit" class="snd-auth-submit">{{ __('Request reset link') }} <x-snd-icon name="arrow-up-right" /></button>
    </form>

    <div class="snd-auth-links"><a href="{{ route('login') }}">Back to sign in</a>@if(Route::has('register'))<a href="{{ route('register') }}">Create account</a>@endif</div>
@endsection
