<nav class="layout-navbar navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <!-- Sidebar Brand -->
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo">
                <img src="{{ asset('logokti.jpg') }}" alt="KTI Logo" style="height: 55px; width: auto;">
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2 text-uppercase text-dark">
                Kutai Timber
                <micro class="d-block text-uppercase text-dark" style="font-size: 0.75rem;">Indonesia</micro>
            </span>
        </a>
    </div>

    <div class="container-fluid">

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            <!-- User Dropdown -->
            <ul class="navbar-nav flex-row align-items-center ms-auto">
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" alt
                                class="w-px-40 h-auto rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}" alt
                                                class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                        <small class="text-muted">{{ Auth::user()->email }}</small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Log Out</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
            <!--/ User Dropdown -->
        </div>
    </div>
</nav>
