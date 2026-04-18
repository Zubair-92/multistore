<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --admin-ink: #172033;
            --admin-night: #101728;
            --admin-panel: #16213a;
            --admin-muted: #6b7280;
            --admin-soft: #f4f6fb;
            --admin-card: rgba(255, 255, 255, 0.9);
            --admin-border: rgba(23, 32, 51, 0.08);
            --admin-accent: #4f46e5;
            --admin-teal: #0f766e;
        }

        body {
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(79, 70, 229, 0.12), transparent 26%),
                linear-gradient(180deg, #f8f9fc 0%, #eef2f7 100%);
            color: var(--admin-ink);
        }

        .content {
            margin-left: 260px;
            margin-top: 78px;
            padding: 24px;
            transition: all 0.3s;
        }

        @media (max-width: 991px) {
            .content { margin-left: 0; margin-top: 78px; }
        }

        .admin-topbar {
            background: rgba(16, 23, 40, 0.92);
            backdrop-filter: blur(16px);
            box-shadow: 0 14px 38px rgba(16, 23, 40, 0.18);
        }

        .admin-sidebar {
            width: 260px;
            height: calc(100vh - 58px);
            position: fixed;
            top: 58px;
            left: 0;
            background:
                radial-gradient(circle at top, rgba(79, 70, 229, 0.28), transparent 24%),
                linear-gradient(180deg, var(--admin-panel) 0%, var(--admin-night) 100%);
            padding: 20px 14px 28px;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .admin-sidebar a {
            color: rgba(255, 255, 255, 0.82);
            padding: 12px 16px;
            display: block;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            border-radius: 14px;
            margin-bottom: 6px;
        }

        .admin-sidebar a:hover,
        .admin-sidebar a:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .admin-sidebar .collapse a {
            padding-left: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.74);
        }

        @media (max-width: 991px) {
            .admin-sidebar { left: -260px; }
            .admin-sidebar.active { left: 0; }
        }

        .admin-shell-card {
            border: 1px solid var(--admin-border) !important;
            border-radius: 20px;
            background: var(--admin-card);
            backdrop-filter: blur(14px);
            box-shadow: 0 18px 50px rgba(23, 32, 51, 0.08);
        }

        .admin-shell-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(23, 32, 51, 0.06);
        }

        .admin-kpi-card {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(23, 32, 51, 0.08);
        }

        .admin-kpi-card .card-body {
            padding: 1.35rem;
        }

        .admin-kpi-primary {
            background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 100%);
            color: #fff;
        }

        .admin-kpi-success {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
            color: #fff;
        }

        .admin-kpi-warm {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
            color: #fff;
        }

        .admin-kpi-dark {
            background: linear-gradient(135deg, #172033 0%, #364152 100%);
            color: #fff;
        }

        .admin-section-title {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.45);
            padding: 10px 14px 6px;
        }

        .admin-alert {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(23, 32, 51, 0.08);
        }

        .table-responsive {
            border-radius: 1rem;
        }

        .pagination {
            --bs-pagination-color: var(--admin-ink);
            --bs-pagination-bg: rgba(255, 255, 255, 0.92);
            --bs-pagination-border-color: rgba(23, 32, 51, 0.12);
            --bs-pagination-hover-color: var(--admin-ink);
            --bs-pagination-hover-bg: rgba(79, 70, 229, 0.1);
            --bs-pagination-hover-border-color: rgba(79, 70, 229, 0.24);
            --bs-pagination-active-bg: var(--admin-accent);
            --bs-pagination-active-border-color: var(--admin-accent);
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .page-link {
            border-radius: 0.85rem !important;
            padding: 0.55rem 0.9rem;
        }

        .menu-toggle { cursor: pointer; }

        @media (max-width: 767px) {
            .table-responsive table {
                min-width: 920px;
            }

            .pagination {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    @php($adminUser = auth('admin')->user())

    <nav class="navbar navbar-dark fixed-top admin-topbar">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 menu-toggle">
                <i class="bi bi-list" style="font-size: 1.5rem;"></i>
            </span>

            <span class="navbar-brand fw-semibold">Admin Panel</span>

            <div class="dropdown">
                <a class="text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($adminUser?->name ?? 'Admin') }}&background=0D8ABC&color=fff" alt="Profile" class="rounded-circle" width="32" height="32">
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text fw-semibold">{{ $adminUser?->name ?? 'Admin User' }}</span></li>
                    <li><span class="dropdown-item-text text-muted small">{{ $adminUser?->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="dropdown-item text-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="admin-sidebar" id="sidebar">
        <div class="admin-section-title">Main</div>
        <a href="{{ route('admin.home') }}">Dashboard</a>

        @if($adminUser?->hasAdminPermission('catalog'))
            <div class="admin-section-title">Catalog</div>
            <a href="{{ route('admin.categories.index') }}">Categories</a>
            <a href="{{ route('admin.subcategories.index') }}">Subcategories</a>
        @endif

        @if($adminUser?->hasAdminPermission('stores') || $adminUser?->hasAdminPermission('catalog'))
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#storesMenu" role="button" aria-expanded="false" aria-controls="storesMenu">
                Stores
            </a>
            <div class="collapse" id="storesMenu">
                @if($adminUser?->hasAdminPermission('catalog'))
                    <a href="{{ route('admin.storecategories.index') }}" class="ps-4">Store Categories</a>
                @endif
                <a href="{{ route('admin.stores.index') }}" class="ps-4">Stores</a>
            </div>
        @endif

        @if($adminUser?->hasAdminPermission('products') || $adminUser?->hasAdminPermission('catalog'))
            <a class="dropdown-toggle" data-bs-toggle="collapse" href="#productsMenu" role="button" aria-expanded="false" aria-controls="productsMenu">
                Products
            </a>
            <div class="collapse" id="productsMenu">
                @if($adminUser?->hasAdminPermission('catalog'))
                    <a href="{{ route('admin.productcategories.index') }}" class="ps-4">Product Categories</a>
                @endif
                @if($adminUser?->hasAdminPermission('products'))
                    <a href="{{ route('admin.products.index') }}" class="ps-4">All Products</a>
                    <a href="{{ route('admin.products.create') }}" class="ps-4">Add Product</a>
                    <a href="{{ route('admin.coupons.index') }}" class="ps-4">Coupons</a>
                @endif
            </div>
        @endif

        @if($adminUser?->hasAdminPermission('orders'))
            <div class="admin-section-title">Operations</div>
            <a href="{{ route('admin.orders.index') }}">Orders</a>
        @endif

        @if($adminUser?->hasAdminPermission('subadmins'))
            <div class="admin-section-title">Access</div>
            <a href="{{ route('admin.subadmins.index') }}">Subadmins</a>
        @endif
    </div>

    <div class="content" id="content">
        @if(session('success'))
            <div class="alert alert-success admin-alert">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger admin-alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.querySelector('.menu-toggle');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
    @if (Route::currentRouteName() == 'admin.productcategories.create')
    <script>
        let categories = @json($categories);
        document.getElementById('category_id').addEventListener('change', function () {
            let categoryId = this.value;
            let subSelect = document.getElementById('sub_category_id');
            subSelect.innerHTML = '<option>Select Subcategory</option>';
            let selectedCategory = categories.find(cat => cat.id == categoryId);
            if(selectedCategory && selectedCategory.subcategory){
                selectedCategory.subcategory.forEach(function(sub){
                    subSelect.innerHTML += `<option value="${sub.id}">${sub.translation?.sub_category ?? ''}</option>`;
                });
            }
        });
    </script>
    @endif
</body>
</html>
