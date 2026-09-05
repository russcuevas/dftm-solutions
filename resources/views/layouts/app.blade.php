<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory & Repair System') - DFTM Solutions</title>

    <!-- DFTM Enterprise Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/dftm-theme.css') }}">

    <!-- Bootstrap Icons Font -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
</head>

<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('images/logo.png') }}" alt="DFTM Logo" class="sidebar-logo">
            </div>

            <ul class="sidebar-menu">
                @if (auth()->user() && auth()->user()->isAdmin())
                    <!-- Admin Navigation -->
                    <li class="menu-heading">Main Overview</li>
                    <li class="menu-item">
                        <a href="{{ route('admin.dashboard') }}"
                            class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-heading">Repair Operations</li>
                    <li class="menu-item">
                        <a href="{{ route('admin.incoming.index') }}"
                            class="menu-link {{ request()->routeIs('admin.incoming.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                            <span>Incoming Slips</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.outgoing.index') }}"
                            class="menu-link {{ request()->routeIs('admin.outgoing.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-box-arrow-up-right"></i></span>
                            <span>Outgoing Slips</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.traceability.index') }}"
                            class="menu-link {{ request()->routeIs('admin.traceability.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-cpu-fill"></i></span>
                            <span>Traceability Matrix</span>
                        </a>
                    </li>

                    <li class="menu-heading">Inventory & Stock</li>
                    <li class="menu-item">
                        <a href="{{ route('admin.inventory.index') }}"
                            class="menu-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-boxes"></i></span>
                            <span>Master Inventory</span>
                        </a>
                    </li>

                    <li class="menu-heading">Administration</li>
                    <li class="menu-item">
                        <a href="{{ route('admin.users.index') }}"
                            class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-people-fill"></i></span>
                            <span>User Accounts</span>
                        </a>
                    </li>
                @else
                    <!-- Encoder Navigation -->
                    <li class="menu-heading">Encoder Portal</li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.dashboard') }}"
                            class="menu-link {{ request()->routeIs('encoder.dashboard') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-speedometer2"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-heading">Encoding Slips</li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.incoming.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.incoming.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-input-cursor-text"></i></span>
                            <span>Encode Incoming</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.outgoing.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.outgoing.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-send-check"></i></span>
                            <span>Process Outgoing</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.traceability.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.traceability.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-tools"></i></span>
                            <span>Repair Matrix</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.inventory.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.inventory.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-archive-fill"></i></span>
                            <span>Stock Status</span>
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Sidebar User Profile -->
            <div class="sidebar-user">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ auth()->user()->name ?? 'User' }}</span>
                        <span
                            class="user-role-badge {{ auth()->user() && auth()->user()->isAdmin() ? 'role-admin' : 'role-encoder' }}">
                            {{ auth()->user()->role ?? 'Encoder' }}
                        </span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-icon" title="Logout"
                        style="color: #F87171; border-color: rgba(248, 113, 113, 0.3);">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="app-main">
            <!-- Header Navbar -->
            <header class="app-navbar">
                <div class="navbar-left">
                    <button type="button" class="btn-sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar" aria-label="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-title-badge">
                        <i class="bi bi-shield-check" style="color: var(--dftm-accent);"></i>
                        <span>@yield('page_title', 'Inventory System')</span>
                    </div>
                </div>
                <div class="navbar-right">
                    <div class="time-badge">
                        <i class="bi bi-clock-history" style="color: var(--dftm-accent);"></i>
                        <span id="liveClock">Loading Manila Time...</span>
                    </div>
                    <a href="{{ route('profile') }}" class="btn btn-outline btn-sm">
                        <i class="bi bi-person-gear"></i> My Profile
                    </a>
                </div>
            </header>

            <!-- Page Body -->
            <main class="page-container">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Core App JS -->
    <script src="{{ asset('js/dftm-app.js') }}"></script>
    @stack('scripts')
</body>

</html>
