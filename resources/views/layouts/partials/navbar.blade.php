<nav class="main-header navbar navbar-expand snd-topbar" aria-label="Primary navigation">
    <button class="snd-menu-button" type="button" data-sidebar-toggle aria-controls="pos-sidebar" aria-expanded="true" aria-label="Toggle sidebar" title="Toggle sidebar">
        <x-snd-icon name="menu" />
    </button>

    <div class="snd-search d-none d-md-block">
        <x-snd-icon name="search" />
        <input type="search" aria-label="Search" placeholder="Search products, orders or customers…">
    </div>

    <div class="snd-topbar-actions">
        <a href="{{ route('cart.index') }}" class="btn btn-primary d-none d-sm-inline-flex align-items-center" style="min-height:32px;padding:0 12px;border-radius:10px!important;">
            <x-snd-icon name="shopping-cart" class="mr-1" />{{ __('POS') }}
        </a>

        <div class="nav-item dropdown">
            <a class="snd-icon-button d-inline-flex align-items-center justify-content-center" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="Language">
                <x-snd-icon name="languages" />
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-label="Language">
                <div class="px-2 py-1 text-uppercase" style="font-size:.58rem;color:var(--snd-muted);font-weight:600;letter-spacing:.12em;">Language</div>
                <a href="{{ route('lang.switch', ['lang' => 'en']) }}" class="dropdown-item {{ app()->getLocale() === 'en' ? 'active-lang' : '' }}">
                    <x-snd-icon name="check" class="mr-2" style="{{ app()->getLocale() === 'en' ? '' : 'visibility:hidden;' }}" />English
                </a>
                <a href="{{ route('lang.switch', ['lang' => 'es']) }}" class="dropdown-item {{ app()->getLocale() === 'es' ? 'active-lang' : '' }}">
                    <x-snd-icon name="check" class="mr-2" style="{{ app()->getLocale() === 'es' ? '' : 'visibility:hidden;' }}" />Español
                </a>
            </div>
        </div>

        <button class="snd-icon-button" type="button" title="Notifications" aria-label="Notifications">
            <x-snd-icon name="bell" />
            <span class="snd-notification-dot" aria-hidden="true"></span>
        </button>

        <div class="nav-item dropdown">
            <a class="p-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" title="{{ auth()->user()->getFullname() }}">
                <span class="snd-user-trigger">
                    <span class="snd-user-avatar">{{ strtoupper(substr(auth()->user()->getFullname(), 0, 1)) }}</span>
                    <span class="snd-user-trigger-text d-none d-md-flex">
                        <strong>{{ auth()->user()->getFullname() }}</strong>
                        <span>{{ auth()->user()->email }}</span>
                    </span>
                    <x-snd-icon name="chevron-down" class="d-none d-md-inline" />
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                <div class="px-2 py-2 mb-1" style="border-bottom:1px solid var(--snd-border);">
                    <div style="font-size:.72rem;font-weight:500;color:var(--snd-ink);">{{ auth()->user()->getFullname() }}</div>
                    <div style="font-size:.62rem;color:var(--snd-muted);">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('settings.index') }}" class="dropdown-item"><x-snd-icon name="settings" class="mr-2" />{{ __('settings.title') }}</a>
                <a href="{{ route('home') }}" class="dropdown-item"><x-snd-icon name="dashboard" class="mr-2" />{{ __('dashboard.title') }}</a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item snd-danger-item" onclick="event.preventDefault(); document.getElementById('navbar-logout-form').submit();">
                    <x-snd-icon name="log-out" class="mr-2" />{{ __('common.Logout') }}
                </a>
                <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </div>
</nav>
