@php
    $brandName = trim(\App\Helpers\CommonHelper::getBrandName());
    $brandSubtitle = trim(\App\Helpers\CommonHelper::getBrandSubtitle());
@endphp

<aside class="main-sidebar snd-sidebar" id="pos-sidebar" aria-label="Sidebar navigation">
    <div class="brand-link snd-brand" title="{{ $brandName }}">
        <a href="{{ route('home') }}" class="snd-brand-home" aria-label="{{ $brandName }}">
            <span class="snd-brand-mark snd-salevince-mark">
                <img src="{{ asset('images/snd-brand-mark.png') }}" alt="" aria-hidden="true">
            </span>
            <span class="snd-brand-copy">
                <span class="snd-brand-title">{{ $brandName }}</span>
                <span class="snd-brand-subtitle">{{ $brandSubtitle }}</span>
            </span>
        </a>
        <button type="button" class="snd-sidebar-close" data-sidebar-close aria-label="Close sidebar">
            <x-snd-icon name="x" />
        </button>
    </div>

    <div class="sidebar snd-sidebar-body">
        <nav aria-label="Application navigation">
            <ul class="nav nav-pills nav-sidebar flex-column snd-nav-list" data-widget="treeview" role="menu" data-accordion="false">
                @can('dashboard.view')
                    <li class="nav-item" data-label="{{ __('dashboard.title') }}">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" data-tooltip="{{ __('dashboard.title') }}" title="{{ __('dashboard.title') }}">
                            <x-snd-icon name="dashboard" class="nav-icon" /><p>{{ __('dashboard.title') }}</p>
                        </a>
                    </li>
                @endcan

                @can('products.view')
                    <li class="nav-item" data-label="{{ __('product.title') }}">
                        <a href="{{ route('products.index') }}" class="nav-link {{ activeSegment('products') }}" data-tooltip="{{ __('product.title') }}" title="{{ __('product.title') }}">
                            <x-snd-icon name="package" class="nav-icon" /><p>{{ __('product.title') }}</p>
                        </a>
                    </li>
                @endcan

                @can('categories.view')
                    <li class="nav-item" data-label="Categories">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ activeSegment('categories') }}" data-tooltip="Categories" title="Categories">
                            <x-snd-icon name="list" class="nav-icon" /><p>Categories</p>
                        </a>
                    </li>
                @endcan

                @can('services.view')
                    <li class="nav-item" data-label="Services">
                        <a href="{{ route('services.index') }}" class="nav-link {{ activeSegment('services') }}" data-tooltip="Services" title="Services">
                            <x-snd-icon name="briefcase" class="nav-icon" /><p>Services</p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item" data-label="Deals">
                    <a href="{{ route('deals.index') }}" class="nav-link {{ activeSegment('deals') }}" data-tooltip="Deals" title="Deals">
                        <x-snd-icon name="tags" class="nav-icon" /><p>Deals</p>
                    </a>
                </li>

                @can('orders.view')
                    <li class="nav-item" data-label="{{ __('POS') }}">
                        <a href="{{ route('cart.index') }}" class="nav-link {{ activeSegment('cart') }}" data-tooltip="{{ __('POS') }}" title="{{ __('POS') }}">
                            <x-snd-icon name="shopping-cart" class="nav-icon" /><p>{{ __('POS') }}</p>
                        </a>
                    </li>
                    <li class="nav-item" data-label="{{ __('Order List') }}">
                        <a href="{{ route('orders.index') }}" class="nav-link {{ activeSegment('orders') }}" data-tooltip="{{ __('Order List') }}" title="{{ __('Order List') }}">
                            <x-snd-icon name="receipt" class="nav-icon" /><p>{{ __('Order List') }}</p>
                        </a>
                    </li>
                @endcan

                @can('customers.view')
                    <li class="nav-item" data-label="{{ __('customer.title') }}">
                        <a href="{{ route('customers.index') }}" class="nav-link {{ activeSegment('customers') }}" data-tooltip="{{ __('customer.title') }}" title="{{ __('customer.title') }}">
                            <x-snd-icon name="users" class="nav-icon" /><p>{{ __('customer.title') }}</p>
                        </a>
                    </li>
                @endcan

                @can('purchases.view')
                    <li class="nav-item {{ request()->routeIs('purchases.*') ? 'menu-open' : '' }}" data-label="{{ __('Purchases') }}">
                        <a href="#" class="nav-link {{ activeSegment('purchases') }}" data-tooltip="{{ __('Purchases') }}" title="{{ __('Purchases') }}" aria-haspopup="true">
                            <x-snd-icon name="shopping-bag" class="nav-icon" /><p>{{ __('Purchases') }}</p><x-snd-icon name="chevron-down" class="right" />
                        </a>
                        <ul class="nav nav-treeview" data-header="{{ __('Purchases') }}">
                            @can('purchases.create')
                                <li class="nav-item"><a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}"><x-snd-icon name="plus" class="nav-icon" /><p>{{ __('New Purchase') }}</p></a></li>
                            @endcan
                            @can('purchases.view')
                                <li class="nav-item"><a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}"><x-snd-icon name="list" class="nav-icon" /><p>{{ __('All Purchases') }}</p></a></li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('suppliers.view')
                    <li class="nav-item" data-label="{{ __('Supplier') }}">
                        <a href="{{ route('suppliers.index') }}" class="nav-link {{ activeSegment('suppliers') }}" data-tooltip="{{ __('Supplier') }}" title="{{ __('Supplier') }}">
                            <x-snd-icon name="truck" class="nav-icon" /><p>{{ __('Supplier') }}</p>
                        </a>
                    </li>
                @endcan

                @can('branches.view')
                    <li class="nav-item" data-label="{{ __('Branches') }}">
                        <a href="{{ route('branches.index') }}" class="nav-link {{ activeSegment('branches') }}" data-tooltip="{{ __('Branches') }}" title="{{ __('Branches') }}">
                            <x-snd-icon name="git-branch" class="nav-icon" /><p>{{ __('Branches') }}</p>
                        </a>
                    </li>
                @endcan

                @can('counters.view')
                    <li class="nav-item" data-label="{{ __('Counters') }}">
                        <a href="{{ route('counters.index') }}" class="nav-link {{ activeSegment('counters') }}" data-tooltip="{{ __('Counters') }}" title="{{ __('Counters') }}">
                            <x-snd-icon name="monitor" class="nav-icon" /><p>{{ __('Counters') }}</p>
                        </a>
                    </li>
                @endcan

                @can('roles.view')
                    <li class="nav-item" data-label="{{ __('Roles') }}">
                        <a href="{{ route('roles.index') }}" class="nav-link {{ activeSegment('roles') }}" data-tooltip="{{ __('Roles') }}" title="{{ __('Roles') }}">
                            <x-snd-icon name="shield" class="nav-icon" /><p>{{ __('Roles') }}</p>
                        </a>
                    </li>
                @endcan

                @can('users.view')
                    <li class="nav-item" data-label="{{ __('Users') }}">
                        <a href="{{ route('users.index') }}" class="nav-link {{ activeSegment('users') }}" data-tooltip="{{ __('Users') }}" title="{{ __('Users') }}">
                            <x-snd-icon name="user-cog" class="nav-icon" /><p>{{ __('Users') }}</p>
                        </a>
                    </li>
                @endcan

                @can('settings.view')
                    <li class="nav-item" data-label="{{ __('settings.title') }}">
                        <a href="{{ route('settings.index') }}" class="nav-link {{ activeSegment('settings') }}" data-tooltip="{{ __('settings.title') }}" title="{{ __('settings.title') }}">
                            <x-snd-icon name="settings" class="nav-icon" /><p>{{ __('settings.title') }}</p>
                        </a>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>
