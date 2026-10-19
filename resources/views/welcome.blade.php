@extends('layouts.auth')
@section('title', 'Knowhere POS')
@section('content')
    <span class="snd-auth-form-index">{{ __('Your workspace') }}</span>
    <h2 class="snd-auth-title">{{ __('Ready when you are.') }}</h2>
    <p class="snd-auth-subtitle">{{ __('Open your dashboard and keep every service sale in focus.') }}</p>
    @auth
        <a href="{{ route('home') }}" class="snd-auth-submit">{{ __('Open dashboard') }} <x-snd-icon name="arrow-up-right" /></a>
    @else
        @if (Route::has('login'))<a href="{{ route('login') }}" class="snd-auth-submit">{{ __('Sign in') }} <x-snd-icon name="arrow-up-right" /></a>@endif
        @if (Route::has('register'))<div class="snd-auth-links"><span>{{ __('New to the workspace?') }}</span><a href="{{ route('register') }}">{{ __('Create account') }}</a></div>@endif
    @endauth
@endsection
