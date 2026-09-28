@extends('layouts.auth')

@section('title', 'Confirm Password - ' . \App\Helpers\CommonHelper::getBrandName())

@section('content')
    <h2 class="snd-auth-title">Confirm password</h2>
    <p class="snd-auth-subtitle">Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="password">Password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="lock" />
                <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required autocomplete="current-password" placeholder="Password">
            </div>
            @error('password')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <button type="submit" class="snd-auth-submit">Confirm password</button>
    </form>

    @if (Route::has('password.request'))
        <div class="snd-auth-links"><a href="{{ route('password.request') }}">Forgot your password?</a><a href="{{ route('login') }}">Sign in</a></div>
    @endif
@endsection
