@extends('layouts.auth')

@section('title', 'Register - ' . \App\Helpers\CommonHelper::getBrandName())

@section('content')
    <h2 class="snd-auth-title">Create your account</h2>
    <p class="snd-auth-subtitle">Set up your workspace access in a few quick details.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="row">
            <div class="col-sm-6">
                <div class="snd-auth-field">
                    <label class="snd-auth-label" for="first_name">First name</label>
                    <div class="snd-auth-input-wrap">
                        <x-snd-icon name="user" />
                        <input id="first_name" type="text" class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}" name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus placeholder="First name">
                    </div>
                    @error('first_name')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="snd-auth-field">
                    <label class="snd-auth-label" for="last_name">Last name</label>
                    <div class="snd-auth-input-wrap">
                        <x-snd-icon name="user" />
                        <input id="last_name" type="text" class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}" name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name" placeholder="Last name">
                    </div>
                    @error('last_name')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
                </div>
            </div>
        </div>
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="email">Email address</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="mail" />
                <input id="email" type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email address">
            </div>
            @error('email')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="password">Password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="lock" />
                <input id="password" type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required autocomplete="new-password" placeholder="Password">
            </div>
            @error('password')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="snd-auth-field">
            <label class="snd-auth-label" for="password-confirm">Confirm password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="circle-check" />
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password">
            </div>
        </div>
        <button type="submit" class="snd-auth-submit">Create account</button>
    </form>
    <div class="snd-auth-links"><span style="color:var(--snd-muted);">Already registered?</span><a href="{{ route('login') }}">Sign in</a></div>
@endsection
