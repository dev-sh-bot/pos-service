@php
    $brandName = trim(\App\Helpers\CommonHelper::getBrandName());
    $brandSubtitle = trim(\App\Helpers\CommonHelper::getBrandSubtitle());
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    @include('layouts.partials.favicon')
    <title>@yield('title', $brandName)</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('layouts.partials.icon-fonts')
    @yield('css')
</head>

<body class="hold-transition snd-auth-page">
    <main class="snd-auth-shell">
        <div class="snd-auth-glow snd-auth-glow-primary" aria-hidden="true"></div>
        <div class="snd-auth-glow snd-auth-glow-secondary" aria-hidden="true"></div>

        <div class="snd-auth-grid">
            <section class="snd-auth-visual" aria-label="{{ $brandName }} workspace">
                <div class="snd-auth-badge">
                    <span class="snd-auth-badge-mark">
                        <img src="{{ asset('images/snd-brand-mark.png') }}" alt="" aria-hidden="true">
                    </span>
                    <span>{{ $brandName }} · Services</span>
                </div>

                <div class="snd-auth-visual-content">
                    <div class="snd-auth-kicker">{{ strtoupper($brandName) }}</div>
                    <h1>Run every service sale with clarity.</h1>
                    <p>Manage services, deals, customers, and payments from one focused service POS workspace.</p>
                </div>

                <div class="snd-auth-feature-grid">
                    <div class="snd-auth-feature"><x-snd-icon name="briefcase" /><strong>Service catalog</strong><span>Organize treatments and packages fast.</span></div>
                    <div class="snd-auth-feature"><x-snd-icon name="receipt" /><strong>Quick checkout</strong><span>Move from booking to receipt quickly.</span></div>
                    <div class="snd-auth-feature"><x-snd-icon name="shield" /><strong>Branch control</strong><span>Keep every counter secure.</span></div>
                </div>
            </section>

            <section class="snd-auth-form-side" aria-label="{{ $brandName }} sign in">
                <div class="snd-auth-card">
                    <div class="snd-auth-brand">
                        <span class="snd-brand-mark snd-salevince-mark"><img src="{{ asset('images/snd-brand-mark.png') }}" alt="" aria-hidden="true"></span>
                        <span><strong>{{ $brandName }}</strong><span>{{ $brandSubtitle }}</span></span>
                    </div>
                    <div class="snd-auth-card-content">
                        @yield('content')
                    </div>
                </div>
            </section>
        </div>
    </main>

    @yield('js')
</body>

</html>
