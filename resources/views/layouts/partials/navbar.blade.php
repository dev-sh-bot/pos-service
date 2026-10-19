@php
    $moduleLabels = [
        'cart' => __('POS'),
        'orders' => __('Order List'),
        'products' => __('product.title'),
        'categories' => __('Categories'),
        'services' => __('Services'),
        'deals' => __('Deals'),
        'customers' => __('customer.title'),
        'purchases' => __('Purchases'),
        'suppliers' => __('Supplier'),
        'branches' => __('Branches'),
        'counters' => __('Counters'),
        'users' => __('Users'),
        'roles' => __('Roles'),
        'permissions' => __('Permissions'),
        'settings' => __('settings.title'),
    ];
    $currentModule = request()->routeIs('home')
        ? __('dashboard.title')
        : ($moduleLabels[request()->segment(2)] ?? __('dashboard.title'));
    $profileInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim(auth()->user()->getFullname()), 0, 1) ?: 'U');
@endphp

<header class="main-header navbar navbar-expand snd-topbar" aria-label="{{ __('Workspace') }}">
    <div class="snd-topbar-leading">
        <button class="snd-menu-button" type="button" data-sidebar-toggle aria-controls="pos-sidebar" aria-expanded="true" aria-label="{{ __('Toggle sidebar') }}" title="{{ __('Toggle sidebar') }}">
            <x-snd-icon name="menu" />
        </button>
        <div class="snd-topbar-context">
            <span>{{ __('Workspace') }}</span>
            <strong>{{ $currentModule }}</strong>
        </div>
    </div>

    <div class="snd-topbar-actions">
        @include('layouts.partials.theme-toggle')
        <div class="dropdown snd-profile-dropdown">
            <button class="snd-user-trigger" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="{{ __('Account') }}: {{ auth()->user()->getFullname() }}" title="{{ auth()->user()->getFullname() }}">
                <img class="snd-user-avatar" data-profile-avatar src="{{ auth()->user()->getAvatar() }}?d=404&s=72" alt="" aria-hidden="true">
                <span class="snd-user-fallback-initial" data-profile-initial-fallback aria-hidden="true" hidden>{{ $profileInitial }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right snd-profile-menu" aria-label="{{ __('Account') }}">
                <div class="snd-profile-summary">
                    <strong>{{ auth()->user()->getFullname() }}</strong>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </div>
                <a href="{{ route('settings.index') }}" class="dropdown-item"><x-snd-icon name="settings" class="mr-2" />{{ __('settings.title') }}</a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item snd-danger-item" onclick="event.preventDefault(); document.getElementById('navbar-logout-form').submit();">
                    <x-snd-icon name="log-out" class="mr-2" />{{ __('common.Logout') }}
                </a>
                <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </div>
</header>
