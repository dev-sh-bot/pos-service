@extends('layouts.auth')

@section('title', 'Verify Email - Knowhere POS')

@section('content')
    <span class="snd-auth-form-index">{{ __('Check your inbox') }}</span>
    <div class="snd-auth-verification-icon"><x-snd-icon name="mail-open" /></div>
    <h2 class="snd-auth-title">{{ __('Verify your email') }}<span>.</span></h2>
    <p class="snd-auth-subtitle">{{ __('Before continuing, please check your email for a verification link.') }}</p>

    @if (session('resent'))
        <div class="alert alert-success" role="alert">{{ __('A fresh verification link has been sent to your email address.') }}</div>
    @endif

    <p class="snd-auth-resend-label">{{ __('If you did not receive the email') }},</p>
    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="snd-auth-submit">{{ __('click here to request another') }} <x-snd-icon name="arrow-up-right" /></button>
    </form>
    <div class="snd-auth-links"><a href="{{ route('login') }}">Back to sign in</a></div>
@endsection
