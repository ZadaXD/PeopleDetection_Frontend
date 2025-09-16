<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <img src="{{ asset('ktilogo.webp') }}" alt="KTI Logo" width="95px">
            <div class="brand-text">
                <span><b>Kutai Timber Indonesia</b></span>
            </div>
        </a>
    </div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>
    </ul>
</aside>
