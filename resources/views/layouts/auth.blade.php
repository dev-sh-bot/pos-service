<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <meta name="color-scheme" content="light dark">
    @include('layouts.partials.theme-bootstrap')
    @include('layouts.partials.favicon')
    <title>@yield('title', 'Knowhere POS')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('layouts.partials.icon-fonts')
    @yield('css')
</head>
<body class="hold-transition snd-auth-page">
    <main class="snd-auth-shell">
        <header class="snd-auth-topline">
            <a href="{{ url('/') }}" class="snd-auth-wordmark" aria-label="Knowhere Systems POS"><span class="snd-auth-logo"><img src="{{ asset('images/knowhere-target.svg') }}" alt=""></span><span>Knowhere<small>Systems / POS</small></span></a>
            <div class="snd-auth-header-actions">
                <span class="snd-auth-system-label"><span aria-hidden="true"></span>{{ __('Service POS workspace') }}</span>
                @include('layouts.partials.theme-toggle')
            </div>
        </header>
        <div class="snd-auth-grid">
            <section class="snd-auth-visual" aria-label="Knowhere Systems {{ __('workspace') }}">
                <div class="snd-auth-visual-content">
                    <div class="snd-auth-kicker"><span>01 /</span> {{ __('Built for your business') }}</div>
                    <h1>{{ __('Every sale.') }}<br><em>{{ __('In focus.') }}</em></h1>
                    <p>{{ __('One workspace for your services, deals, customers, and payments. Keep your business moving.') }}</p>
                </div>
                <div class="snd-auth-flow" aria-hidden="true">
                    <div><span>01</span><x-snd-icon name="briefcase" /><strong>{{ __('Catalog') }}</strong></div>
                    <x-snd-icon name="arrow-right" />
                    <div><span>02</span><x-snd-icon name="shopping-cart" /><strong>{{ __('Checkout') }}</strong></div>
                    <x-snd-icon name="arrow-right" />
                    <div class="snd-auth-flow-complete"><span>03</span><x-snd-icon name="circle-check" /><strong>{{ __('Complete') }}</strong></div>
                </div>
                <div class="snd-auth-visual-footer"><x-snd-icon name="git-branch" /><span>{{ __('Your branches. Your counters. Connected.') }}</span></div>
            </section>
            <section class="snd-auth-form-side" aria-label="Knowhere Systems {{ __('account access') }}">
                <div class="snd-auth-card">
                    <div class="snd-auth-card-top"><span>{{ __('Account access') }}</span><x-snd-icon name="arrow-up-right" /></div>
                    <div class="snd-auth-card-content">
                        @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
                        @yield('content')
                    </div>
                    <div class="snd-auth-card-footer"><x-snd-icon name="shield" /><span>{{ __('Your account. Your workspace.') }}</span></div>
                </div>
            </section>
        </div>
        <footer class="snd-auth-footer"><span>&copy; {{ now()->year }} Knowhere Systems</span><span>{{ __('Services / Deals / Billing') }}</span></footer>
    </main>
    @yield('js')
</body>
</html>
