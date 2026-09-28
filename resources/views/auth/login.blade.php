@extends('layouts.auth')

@section('title', 'Sign In - ' . \App\Helpers\CommonHelper::getBrandName())

@section('content')
    <h2 class="snd-auth-title">Welcome back</h2>
    <p class="snd-auth-subtitle">Sign in to continue to your service POS dashboard.</p>

    <form action="{{ route('login') }}" method="post">
        @csrf

        <div class="snd-auth-field">
            <label class="snd-auth-label" for="email">Email address</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="mail" />
                <input id="email" type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email address" value="{{ old('email') }}" required autocomplete="email" autofocus>
            </div>
            @error('email')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>

        <div class="snd-auth-field">
            <label class="snd-auth-label" for="lp-pwd">Password</label>
            <div class="snd-auth-input-wrap">
                <x-snd-icon name="lock" />
                <input type="password" id="lp-pwd" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Password" required autocomplete="current-password">
                <button type="button" class="lg-eye" onclick="togglePwd()" aria-label="Show password"><x-snd-icon name="eye" id="lg-eye-i" /></button>
            </div>
            @error('password')<span class="snd-auth-error"><strong>{{ $message }}</strong></span>@enderror
        </div>

        <div class="d-flex justify-content-end mb-2">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="font-size:.68rem;font-weight:500;color:var(--snd-primary-deep);">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="snd-auth-submit">Sign in</button>
    </form>
@endsection

@section('js')
<script>
function togglePwd() {
    var input = document.getElementById('lp-pwd');
    var icon = document.getElementById('lg-eye-i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.innerHTML = input.type === 'password'
        ? '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M10.733 5.076a10.744 10.744 0 0 1 9.208 6.58 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-4.7 5.395"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>';
}
</script>
@endsection
