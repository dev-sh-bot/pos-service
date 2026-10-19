<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    @include('layouts.partials.theme-bootstrap')
    <title>@yield('title', 'Knowhere POS')</title>
    @include('layouts.partials.favicon')
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('layouts.partials.icon-fonts')
    @yield('css')
</head>
<body class="snd-legacy-page">
    <div id="app">
        <nav class="navbar navbar-expand-md snd-legacy-navbar">
            <div class="container">
                <a class="snd-auth-wordmark" href="{{ url('/') }}" aria-label="Knowhere Systems POS"><span class="snd-auth-logo"><img src="{{ asset('images/knowhere-target.svg') }}" alt=""></span><span>Knowhere<small>Systems / POS</small></span></a>
                @include('layouts.partials.theme-toggle')
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}"><x-snd-icon name="menu" /></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                        @guest
                            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }} <x-snd-icon name="arrow-up-right" /></a></li>
                            @if (Route::has('register'))<li class="nav-item"><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>@endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>{{ Auth::user()->getFullname() }}</a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><x-snd-icon name="log-out" /> {{ __('Logout') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <main class="container snd-legacy-content">@yield('content')</main>
    </div>
    @yield('js')
</body>
</html>
