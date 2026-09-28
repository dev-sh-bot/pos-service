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

    {{-- Keep this runtime contract stable for Cart.jsx and other page scripts. --}}
    <script>
        window.APP = <?php echo json_encode([
            'currency_symbol'  => config('settings.currency_symbol'),
            'app_name'         => config('settings.app_name', config('app.name')),
            'app_description'  => config('settings.app_description', ''),
            'business_logo_url' => config('settings.business_logo')
                ? (\Illuminate\Support\Str::startsWith(config('settings.business_logo'), ['http://', 'https://'])
                    ? config('settings.business_logo')
                    : asset('storage/' . config('settings.business_logo')))
                : null,
            'srb_pos_logo_url' => \Illuminate\Support\Facades\Storage::disk('public')->url('settings/srb-pos-logo.jpg'),
            'warning_quantity' => config('settings.warning_quantity'),
            'show_products'    => (bool) config('settings.show_products', true),
            'show_services'    => (bool) config('settings.show_services', true),
            'srb_enabled'      => (bool) config('settings.srb_enabled', false),
            'tax_enabled'      => (bool) config('settings.tax_enabled', false),
            'discount_enabled' => (bool) config('settings.discount_enabled', false),
            'editable_item_rate' => (bool) config('settings.editable_item_rate', false),
            'locale_cart_url'  => url('/admin/locale/cart'),
            'pos_access' => [
                'status'         => route('pos-access.status'),
                'branches'       => route('pos-access.branches'),
                'verify_branch'  => route('pos-access.verify-branch'),
                'counters'       => route('pos-access.counters'),
                'verify_counter' => route('pos-access.verify-counter'),
                'clear'          => route('pos-access.clear'),
            ],
        ]); ?>;
    </script>
</head>

<body class="hold-transition snd-admin">
    <div class="snd-app-shell">
            @include('layouts.partials.sidebar')

        <div class="snd-mobile-overlay" data-sidebar-overlay></div>

        <div class="snd-main">
            <div class="snd-utility-bar">
                <span class="snd-utility-brand">
                    <span class="snd-utility-mark snd-salevince-utility-mark">
                        <img src="{{ asset('images/snd-brand-mark.png') }}" alt="" aria-hidden="true">
                    </span>
                    <span>{{ $brandName }} <small>{{ $brandSubtitle }}</small></span>
                </span>
            </div>

            @include('layouts.partials.navbar')

            <div class="content-wrapper snd-content-wrapper">
                <section class="content-header snd-content-header">
                    <div class="snd-content-header-inner">
                        <div class="snd-page-heading">
                            @hasSection('content-eyebrow')
                                <div class="snd-eyebrow">@yield('content-eyebrow')</div>
                            @endif
                            @if(trim($__env->yieldContent('content-header')) !== '')
                                <h1 class="snd-page-title">@yield('content-header')</h1>
                            @endif
                            @hasSection('content-subtitle')
                                <p class="snd-page-subtitle">@yield('content-subtitle')</p>
                            @endif
                        </div>
                        <div class="snd-content-actions">@yield('content-actions')</div>
                    </div>
                </section>

                <section class="content snd-content">
                    @include('layouts.partials.alert.success')
                    @include('layouts.partials.alert.error')
                    @yield('content')
                </section>
            </div>

        </div>
    </div>

    @yield('js')
    @yield('model')
</body>

</html>
