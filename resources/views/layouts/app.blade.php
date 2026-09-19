<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventory & Repair System') - DFTM Solutions</title>

    <!-- DFTM Enterprise Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/dftm-theme.css') }}?v={{ file_exists(public_path('css/dftm-theme.css')) ? filemtime(public_path('css/dftm-theme.css')) : time() }}">

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

            @if (auth()->user() && auth()->user()->isAdmin())
                <!-- 2 Portal Buttons Switcher -->
                <div class="sidebar-portal-switch">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="portal-tab-btn {{ !request()->routeIs('admin.consumables.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> OFFICE
                    </a>
                    <a href="{{ route('admin.consumables.index') }}" 
                       class="portal-tab-btn {{ request()->routeIs('admin.consumables.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i> CONSUMABLE
                    </a>
                </div>
            @elseif (auth()->user() && auth()->user()->isClient())
                <div style="margin: 0 16px 16px; padding: 10px 14px; background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 8px; display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-shield-check" style="color: #C084FC; font-size: 1.2rem;"></i>
                    <div style="overflow: hidden;">
                        <div style="font-size: 0.68rem; color: #CBD5E1; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Customer Portal</div>
                        <strong style="color: #FFFFFF; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ auth()->user()->company_name ?? 'Client Account' }}</strong>
                    </div>
                </div>
            @endif

            <ul class="sidebar-menu">
                @if (auth()->user() && auth()->user()->isAdmin())
                    @if (!request()->routeIs('admin.consumables.*'))
                        <!-- Office Portal Navigation -->
                        <li class="menu-heading">Office Management</li>
                        <li class="menu-item">
                            <a href="{{ route('admin.dashboard') }}"
                                class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.incoming.index') }}"
                                class="menu-link {{ request()->routeIs('admin.incoming.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                                <span>Incoming</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.traceability.index') }}"
                                class="menu-link {{ request()->routeIs('admin.traceability.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-cpu-fill"></i></span>
                                <span>Repair Traceability Matrix</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.outgoing.index') }}"
                                class="menu-link {{ request()->routeIs('admin.outgoing.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-printer-fill"></i></span>
                                <span>Outgoing Reports</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.inventory.index') }}"
                                class="menu-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-boxes"></i></span>
                                <span>Master Inventory</span>
                            </a>
                        </li>
                    @else
                        <!-- Consumable Portal Navigation -->
                        <li class="menu-heading">Consumable Management</li>
                        <li class="menu-item">
                            <a href="{{ route('admin.consumables.index') }}"
                                class="menu-link {{ request()->routeIs('admin.consumables.index') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-table"></i></span>
                                <span>Monthly Inventory</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.consumables.logs.index') }}"
                                class="menu-link {{ request()->routeIs('admin.consumables.logs.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-clock-history"></i></span>
                                <span>Daily In/Out Logs</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.consumables.items.index') }}"
                                class="menu-link {{ request()->routeIs('admin.consumables.items.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-cart-check-fill"></i></span>
                                <span>Consumable Items</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('admin.consumables.categories.index') }}"
                                class="menu-link {{ request()->routeIs('admin.consumables.categories.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i class="bi bi-tags-fill"></i></span>
                                <span>Categories Manager</span>
                            </a>
                        </li>
                    @endif

                    <li class="menu-heading">Administration</li>
                    <li class="menu-item">
                        <a href="{{ route('admin.users.index') }}"
                            class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-person-gear"></i></span>
                            <span>User Accounts</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('admin.clients.index') }}"
                            class="menu-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-buildings"></i></span>
                            <span>Client Accounts</span>
                        </a>
                    </li>
                @elseif (auth()->user() && auth()->user()->isClient())
                    <!-- Client Portal Navigation (Read-Only Tracking & Reports) -->
                    <li class="menu-heading">Customer Portal</li>
                    <li class="menu-item">
                        <a href="{{ route('client.dashboard') }}"
                            class="menu-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-search"></i></span>
                            <span>Dashboard & Tracker</span>
                        </a>
                    </li>

                    <li class="menu-heading">My Company Records</li>
                    <li class="menu-item">
                        <a href="{{ route('client.incoming.index') }}"
                            class="menu-link {{ request()->routeIs('client.incoming.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                            <span>Incoming</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('client.traceability.index') }}"
                            class="menu-link {{ request()->routeIs('client.traceability.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-diagram-3-fill"></i></span>
                            <span>Repair Traceability Matrix</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('client.outgoing.index') }}"
                            class="menu-link {{ request()->routeIs('client.outgoing.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-printer-fill"></i></span>
                            <span>Outgoing Reports</span>
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

                    <li class="menu-heading">Encoding Flow</li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.incoming.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.incoming.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-input-cursor-text"></i></span>
                            <span>Incoming</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.traceability.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.traceability.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-tools"></i></span>
                            <span>Repair Traceability Matrix</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.outgoing.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.outgoing.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-printer-fill"></i></span>
                            <span>Outgoing Reports</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.inventory.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.inventory.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-archive-fill"></i></span>
                            <span>Stock Status</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('encoder.clients.index') }}"
                            class="menu-link {{ request()->routeIs('encoder.clients.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="bi bi-buildings"></i></span>
                            <span>Clients & Companies</span>
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
                            class="user-role-badge {{ auth()->user() && auth()->user()->isAdmin() ? 'role-admin' : (auth()->user() && auth()->user()->isClient() ? 'role-client' : 'role-encoder') }}">
                            {{ auth()->user() && auth()->user()->isClient() ? (auth()->user()->company_name ?? 'CLIENT') : strtoupper(auth()->user()->role ?? 'ENCODER') }}
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
