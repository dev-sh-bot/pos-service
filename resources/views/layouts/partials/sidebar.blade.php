@php
    $businessName = trim(\App\Helpers\CommonHelper::getBrandName());
    $navGroups = [
        ['label' => __('Overview'), 'items' => [
            ['route' => 'home', 'active' => 'home', 'icon' => 'dashboard', 'label' => __('dashboard.title'), 'permission' => 'dashboard.view'],
        ]],
        ['label' => __('Sales'), 'items' => [
            ['route' => 'cart.index', 'active' => 'cart.*', 'icon' => 'shopping-cart', 'label' => __('POS'), 'permission' => 'orders.view'],
            ['route' => 'orders.index', 'active' => 'orders.*', 'icon' => 'receipt', 'label' => __('Order List'), 'permission' => 'orders.view'],
            ['route' => 'customers.index', 'active' => 'customers.*', 'icon' => 'users', 'label' => __('customer.title'), 'permission' => 'customers.view'],
        ]],
        ['label' => __('Catalog'), 'items' => [
            ['route' => 'products.index', 'active' => 'products.*', 'icon' => 'package', 'label' => __('product.title'), 'permission' => 'products.view'],
            ['route' => 'categories.index', 'active' => 'categories.*', 'icon' => 'list', 'label' => __('Categories'), 'permission' => 'categories.view'],
            ['route' => 'services.index', 'active' => 'services.*', 'icon' => 'briefcase', 'label' => __('Services'), 'permission' => 'services.view'],
            ['route' => 'deals.index', 'active' => 'deals.*', 'icon' => 'tags', 'label' => __('Deals'), 'permission' => null],
        ]],
        ['label' => __('Purchases'), 'items' => [
            ['route' => 'purchases.index', 'active' => 'purchases.*', 'icon' => 'shopping-bag', 'label' => __('Purchases'), 'permission' => 'purchases.view', 'children' => true],
            ['route' => 'suppliers.index', 'active' => 'suppliers.*', 'icon' => 'truck', 'label' => __('Supplier'), 'permission' => 'suppliers.view'],
        ]],
        ['label' => __('Administration'), 'items' => [
            ['route' => 'branches.index', 'active' => 'branches.*', 'icon' => 'git-branch', 'label' => __('Branches'), 'permission' => 'branches.view'],
            ['route' => 'counters.index', 'active' => 'counters.*', 'icon' => 'monitor', 'label' => __('Counters'), 'permission' => 'counters.view'],
            ['route' => 'users.index', 'active' => 'users.*', 'icon' => 'user-cog', 'label' => __('Users'), 'permission' => 'users.view'],
            ['route' => 'roles.index', 'active' => 'roles.*', 'icon' => 'shield', 'label' => __('Roles'), 'permission' => 'roles.view'],
            ['route' => 'permissions.index', 'active' => 'permissions.*', 'icon' => 'key', 'label' => __('Permissions'), 'permission' => 'permissions.view'],
        ]],
        ['label' => __('System'), 'items' => [
            ['route' => 'settings.index', 'active' => 'settings.*', 'icon' => 'settings', 'label' => __('settings.title'), 'permission' => 'settings.view'],
        ]],
    ];
@endphp
<aside class="main-sidebar snd-sidebar" id="pos-sidebar" aria-label="{{ __('Sidebar navigation') }}">
    <div class="brand-link snd-brand" title="{{ $businessName }}">
        <a href="{{ route('home') }}" class="snd-brand-home" aria-label="Knowhere Systems POS" title="Knowhere Systems POS">
            <span class="snd-brand-mark"><img src="{{ asset('images/knowhere-target.svg') }}" alt="" aria-hidden="true"></span>
            <span class="snd-brand-copy"><span class="snd-brand-title">Knowhere</span><span class="snd-brand-subtitle">Systems / POS</span></span>
        </a>
        <button type="button" class="snd-sidebar-close" data-sidebar-close aria-label="{{ __('Close sidebar') }}"><x-snd-icon name="x" /></button>
    </div>
    <div class="sidebar snd-sidebar-body">
        <nav aria-label="{{ __('Application navigation') }}">
            <ul class="nav nav-pills nav-sidebar flex-column snd-nav-list" data-widget="treeview" role="menu" data-accordion="false">
                @foreach($navGroups as $group)
                    @php($visibleItems = array_filter($group['items'], fn($item) => !$item['permission'] || auth()->user()->can($item['permission'])))
                    @if(count($visibleItems))
                        <li class="nav-header" role="presentation">{{ $group['label'] }}</li>
                        @foreach($visibleItems as $item)
                            <li class="nav-item {{ !empty($item['children']) && request()->routeIs($item['active']) ? 'menu-open' : '' }}" data-label="{{ $item['label'] }}">
                                <a href="{{ !empty($item['children']) ? '#' : route($item['route']) }}" class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}" data-tooltip="{{ $item['label'] }}" @if(!empty($item['children'])) aria-haspopup="true" aria-expanded="{{ request()->routeIs($item['active']) ? 'true' : 'false' }}" @elseif(request()->routeIs($item['active'])) aria-current="page" @endif>
                                    <x-snd-icon :name="$item['icon']" class="nav-icon" /><p>{{ $item['label'] }}</p>
                                    @if(!empty($item['children']))<x-snd-icon name="chevron-down" class="right" />@endif
                                </a>
                                @if(!empty($item['children']))
                                    <ul class="nav nav-treeview" data-header="{{ __('Purchases') }}">
                                        @can('purchases.create')
                                            <li class="nav-item"><a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}" aria-label="{{ __('New Purchase') }}" title="{{ __('New Purchase') }}"><x-snd-icon name="plus" class="nav-icon" /><p>{{ __('New Purchase') }}</p></a></li>
                                        @endcan
                                        <li class="nav-item"><a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index') ? 'active' : '' }}" aria-label="{{ __('All Purchases') }}" title="{{ __('All Purchases') }}"><x-snd-icon name="list" class="nav-icon" /><p>{{ __('All Purchases') }}</p></a></li>
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
