@extends('layouts.auth')

@section('title', 'Reset Password - Knowhere POS')

@section('content')
    <span class="snd-auth-form-index">{{ __('Account recovery') }}</span>
    <h2 class="snd-auth-title">{{ __('A fresh start') }}<span>.</span></h2>
    <p class="snd-auth-subtitle">{{ __('Choose a strong new password to keep your account protected.') }}</p>

    <form method="POST" action="{{ route('password.update') }}" class="snd-auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="snd-auth-field">
            <label class="snd-auth-label" for="email">Email address</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="mail" />
                <input id="email" type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus placeholder="Email address">
            </div>
            @error('email')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="password">New password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="lock" />
                <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required autocomplete="new-password" placeholder="New password">
            </div>
            @error('password')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="password-confirm">Confirm new password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="circle-check" />
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm new password">
            </div>
        </div>
        <button type="submit" class="snd-auth-submit">{{ __('Reset password') }} <x-snd-icon name="arrow-up-right" /></button>
    </form>
@endsection
