@extends('layouts.auth')

@section('title', 'Verify Email - ' . \App\Helpers\CommonHelper::getBrandName())

@section('content')
    <div class="snd-card-icon mb-3"><x-snd-icon name="mail-open" /></div>
    <h2 class="snd-auth-title">Verify your email</h2>
    <p class="snd-auth-subtitle">Before continuing, please check your email for a verification link.</p>

    @if (session('resent'))
        <div class="alert alert-success" role="alert">{{ __('A fresh verification link has been sent to your email address.') }}</div>
    @endif

    <p style="font-size:.74rem;color:var(--snd-ink-soft);">{{ __('If you did not receive the email') }},</p>
    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="snd-auth-submit">{{ __('click here to request another') }}</button>
    </form>
    <div class="snd-auth-links"><a href="{{ route('login') }}">Back to sign in</a></div>
@endsection
