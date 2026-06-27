<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS KasirPro')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::asset('/build/img/favicon.png') }}">
    <link rel="manifest" href="{{ URL::asset('/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ URL::asset('/build/img/logo-192.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="KasirPro">
    <meta name="theme-color" content="#092C4C">
    <link rel="stylesheet" href="{{ URL::asset('/build/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('/build/plugins/fontawesome/css/all.min.css') }}">
    @stack('styles')
    <style>
        :root { --primary: #FF9F43; --secondary: #092C4C; }
        body { font-family: 'Nunito', sans-serif; background: #f3f6f9; }
        body.offline::before { content: 'OFFLINE'; position: fixed; top: 10px; right: 10px; background: #FF0000; color: white; padding: 4px 10px; border-radius: 4px; font-size: 11px; z-index: 9999; }
        .sidebar { width: 250px; min-height: 100vh; background: var(--secondary); position: fixed; left: 0; top: 0; z-index: 100; overflow-y: auto; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 12px 20px; display: flex; align-items: center; gap: 12px; text-decoration: none; font-size: 14px; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,159,67,0.2); }
        .sidebar .nav-link i { width: 18px; text-align: center; }
        .sidebar .menu-title { color: rgba(255,255,255,0.4); padding: 15px 20px 5px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar .logo-area { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 10px; }
        .main-content { margin-left: 250px; min-height: 100vh; }
        .topbar { background: #fff; padding: 12px 24px; border-bottom: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
        .page-content { padding: 24px; }
        .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-radius: 8px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 16px 20px; font-weight: 600; }
        .card-body { padding: 20px; }
        .table > thead { background: #f8f9fa; }
        .table > thead th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; border-bottom: none; padding: 12px; }
        .table > tbody td { padding: 12px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
        .stat-card { border-radius: 8px; padding: 20px; border-left: 4px solid; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: #e68a2e; border-color: #e68a2e; }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
        .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .badge.bg-success { background: #28C76F !important; }
        .badge.bg-warning { background: #FF9900 !important; }
        .badge.bg-danger { background: #FF0000 !important; }
        .badge.bg-info { background: #17a2b8 !important; }
        .pagination { justify-content: center; margin-top: 16px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        .alert { border-radius: 8px; }
        .form-control, .form-select { border-radius: 6px; border-color: #e0e0e0; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(255,159,67,0.25); }
        .page-title { font-size: 20px; font-weight: 700; color: #333; }
        .table-responsive { border-radius: 8px; }
        .nav-tabs .nav-link { color: var(--secondary); }
        .nav-tabs .nav-link.active { color: var(--primary); border-bottom-color: var(--primary); }
        .modal-content { border-radius: 12px; border: none; }
        .modal-header { border-bottom: 1px solid #f0f0f0; }
        .dropdown-item:hover { background: rgba(255,159,67,0.1); color: var(--primary); }
        @media (max-width: 768px) { .sidebar { width: 100%; position: fixed; left: -100%; transition: left 0.3s; z-index: 1050; } .sidebar.show { left: 0; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <!-- Mobile overlay -->
    <div id="sidebar-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1049;" onclick="hideSidebar()"></div>

    @guest
        @yield('content')
    @else
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-area d-flex align-items-center gap-2">
            <img src="{{ URL::asset('/build/img/logo-small.png') }}" alt="Logo" height="32">
            <span style="color:#fff; font-weight:700; font-size:18px;">KasirPro</span>
        </div>
        <div class="menu-title">Main</div>
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fas fa-th-large"></i> Dashboard</a>
        <a class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" href="{{ route('pos.index') }}"><i class="fas fa-shopping-cart"></i> POS</a>
        
        <div class="menu-title">Inventory</div>
        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}"><i class="fas fa-box"></i> Products</a>
        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}"><i class="fas fa-tags"></i> Categories</a>
        <a class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}" href="{{ route('brands.index') }}"><i class="fas fa-award"></i> Brands</a>
        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}" href="{{ route('units.index') }}"><i class="fas fa-balance-scale"></i> Units</a>
        <a class="nav-link {{ request()->routeIs('contacts.*','contacts.customers','contacts.suppliers') ? 'active' : '' }}" href="{{ route('contacts.index') }}"><i class="fas fa-users"></i> Contacts</a>

        <div class="menu-title">Transactions</div>
        <a class="nav-link {{ request()->routeIs('sells.*') ? 'active' : '' }}" href="{{ route('sells.index') }}"><i class="fas fa-file-invoice-dollar"></i> Sales</a>
        <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}"><i class="fas fa-truck"></i> Purchases</a>
        <a class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}" href="{{ route('expenses.index') }}"><i class="fas fa-money-bill-wave"></i> Expenses</a>

        <div class="menu-title">Stock</div>
        <a class="nav-link {{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}" href="{{ route('stock-adjustments.index') }}"><i class="fas fa-sliders-h"></i> Stock Adj.</a>
        <a class="nav-link {{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}" href="{{ route('stock-transfers.index') }}"><i class="fas fa-exchange-alt"></i> Stock Transfer</a>

        <div class="menu-title">Finance</div>
        <a class="nav-link {{ request()->routeIs('cash-registers.*') ? 'active' : '' }}" href="{{ route('cash-registers.index') }}"><i class="fas fa-cash-register"></i> Cash Register</a>
        <a class="nav-link {{ request()->routeIs('memberships.*') ? 'active' : '' }}" href="{{ route('memberships.index') }}"><i class="fas fa-award"></i> Memberships</a>

        <div class="menu-title">Others</div>
        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><i class="fas fa-chart-bar"></i> Reports</a>
        <a class="nav-link {{ request()->routeIs('marketplace.*') ? 'active' : '' }}" href="{{ route('marketplace.index') }}"><i class="fas fa-store"></i> Marketplace</a>
        <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="fas fa-cog"></i> Settings</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <button class="btn btn-link d-md-none text-dark p-0 me-2" onclick="toggleSidebar()" style="font-size:20px; text-decoration:none;">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="page-title">@yield('title', 'Dashboard')</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><i class="fas fa-store me-1"></i>{{ session('current_business')->name ?? 'POS KasirPro' }}</span>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle text-dark p-0" style="text-decoration:none;" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i> {{ Auth::user()->name ?? 'User' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebar-overlay').style.display = document.getElementById('sidebar').classList.contains('show') ? 'block' : 'none';
        }
        function hideSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebar-overlay').style.display = 'none';
        }
    </script>
    @endguest

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
            $('[data-bs-toggle="popover"]').popover();
        });

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }

        // Online/Offline indicator
        function updateOnlineStatus() {
            if (navigator.onLine) {
                document.body.classList.remove('offline');
                if (window.syncPendingTransactions) {
                    window.syncPendingTransactions();
                }
            } else {
                document.body.classList.add('offline');
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    </script>
    @stack('scripts')
</body>
</html>
