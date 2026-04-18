<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Multistore Marketplace</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
        <style>
            :root {
                --marketplace-ink: #16213d;
                --marketplace-navy: #1c2f5c;
                --marketplace-sand: #f6efe5;
                --marketplace-gold: #d6a24a;
                --marketplace-rose: #b85c38;
                --marketplace-card: rgba(255, 255, 255, 0.88);
                --marketplace-border: rgba(28, 47, 92, 0.12);
            }

            body.marketplace-shell {
                font-family: "Manrope", sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(214, 162, 74, 0.18), transparent 28%),
                    linear-gradient(180deg, #fbf8f3 0%, #f3ede4 100%);
                color: var(--marketplace-ink);
            }

            .marketplace-navbar {
                background: rgba(251, 248, 243, 0.82);
                backdrop-filter: blur(18px);
                border-bottom: 1px solid rgba(28, 47, 92, 0.08);
            }

            .marketplace-brand {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                font-family: "Fraunces", serif;
                font-weight: 700;
                letter-spacing: 0.02em;
            }

            .marketplace-brand-mark {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.2rem;
                height: 2.2rem;
                border-radius: 0.85rem;
                background: linear-gradient(135deg, var(--marketplace-gold), var(--marketplace-rose));
                color: #fff;
                font-size: 1rem;
                box-shadow: 0 10px 24px rgba(184, 92, 56, 0.28);
            }

            .navbar-light .navbar-nav .nav-link {
                color: rgba(22, 33, 61, 0.72);
                font-weight: 600;
            }

            .navbar-light .navbar-nav .nav-link.active,
            .navbar-light .navbar-nav .nav-link:hover {
                color: var(--marketplace-ink);
            }

            .btn-marketplace-primary {
                background: var(--marketplace-navy);
                border-color: var(--marketplace-navy);
                color: #fff;
            }

            .btn-marketplace-primary:hover,
            .btn-marketplace-primary:focus {
                background: #142445;
                border-color: #142445;
                color: #fff;
            }

            .btn-marketplace-secondary {
                background: rgba(214, 162, 74, 0.12);
                border: 1px solid rgba(214, 162, 74, 0.3);
                color: var(--marketplace-ink);
                font-weight: 700;
            }

            .btn-marketplace-secondary:hover,
            .btn-marketplace-secondary:focus {
                background: rgba(214, 162, 74, 0.2);
                color: var(--marketplace-ink);
            }

            .marketplace-hero {
                background:
                    radial-gradient(circle at top right, rgba(214, 162, 74, 0.25), transparent 30%),
                    linear-gradient(135deg, #182746 0%, #243e73 45%, #a54d32 100%);
            }

            .marketplace-badge {
                background: rgba(255, 255, 255, 0.16);
                color: #fff;
                border: 1px solid rgba(255, 255, 255, 0.25);
                padding: 0.65rem 0.95rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .marketplace-surface-card,
            .marketplace-product-card {
                border: 1px solid var(--marketplace-border) !important;
                background: var(--marketplace-card);
                backdrop-filter: blur(10px);
                border-radius: 1.1rem;
                box-shadow: 0 18px 45px rgba(22, 33, 61, 0.08);
            }

            .marketplace-product-card {
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }

            .marketplace-product-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 22px 55px rgba(22, 33, 61, 0.12);
            }

            .marketplace-metric-tile {
                padding: 0.95rem;
                border-radius: 0.9rem;
                background: rgba(28, 47, 92, 0.05);
                border: 1px solid rgba(28, 47, 92, 0.08);
            }

            .marketplace-alert {
                border-radius: 0.9rem;
                border: 0;
                box-shadow: 0 12px 30px rgba(22, 33, 61, 0.08);
            }

            .marketplace-footer {
                background:
                    radial-gradient(circle at top left, rgba(214, 162, 74, 0.18), transparent 25%),
                    linear-gradient(180deg, #16213d 0%, #10192f 100%);
                color: rgba(255, 255, 255, 0.88);
            }

            .marketplace-footer .text-muted {
                color: rgba(255, 255, 255, 0.62) !important;
            }

            .table-responsive {
                border-radius: 1rem;
            }

            .pagination {
                --bs-pagination-color: var(--marketplace-ink);
                --bs-pagination-bg: rgba(255, 255, 255, 0.92);
                --bs-pagination-border-color: rgba(28, 47, 92, 0.12);
                --bs-pagination-hover-color: var(--marketplace-ink);
                --bs-pagination-hover-bg: rgba(214, 162, 74, 0.14);
                --bs-pagination-hover-border-color: rgba(214, 162, 74, 0.28);
                --bs-pagination-active-bg: var(--marketplace-navy);
                --bs-pagination-active-border-color: var(--marketplace-navy);
                --bs-pagination-active-color: #fff;
                gap: 0.35rem;
                flex-wrap: wrap;
            }

            .page-link {
                border-radius: 0.85rem !important;
                padding: 0.55rem 0.9rem;
                box-shadow: 0 10px 24px rgba(22, 33, 61, 0.06);
            }

            @media (max-width: 991px) {
                .marketplace-navbar .btn-marketplace-secondary {
                    display: none !important;
                }

                .table-responsive table {
                    min-width: 860px;
                }
            }

            @media (max-width: 767px) {
                .pagination {
                    justify-content: center;
                }
            }
        </style>
        <script>
            window.Laravel = {
                csrfToken: "{{ csrf_token() }}",
                routes: {
                    cartAdd: "{{ route('cart.add') }}",
                    cartRemove: "{{ route('cart.remove', ['id' => 'ID_PLACEHOLDER']) }}",
                    cartUpdate: "{{ route('cart.update', ['id' => 'ID_PLACEHOLDER']) }}",
                    checkout: "{{ route('placeorder') }}",
                }
            };
        </script>
    </head>
    <body class="d-flex flex-column min-vh-100 marketplace-shell">
        <nav class="navbar navbar-expand-lg navbar-light marketplace-navbar sticky-top">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand marketplace-brand" href="{{ route('frontend.home') }}">
                    <span class="marketplace-brand-mark">M</span>
                    <span>Multistore</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('frontend.home') ? 'active' : '' }}" aria-current="page" href="{{ route('frontend.home') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('frontend.products.*') ? 'active' : '' }}" href="{{ route('frontend.products.index') }}">Products</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('frontend.stores.*') ? 'active' : '' }}" href="{{ route('frontend.stores.index') }}">Stores</a></li>
                        <li class="nav-item"><a class="nav-link" href="#marketplace-about">About</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Shop</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('frontend.products.index') }}">All Products</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.products.index', ['stock' => 'in_stock']) }}">In Stock</a></li>
                                <li><a class="dropdown-item" href="{{ route('frontend.products.index', ['sort' => 'latest']) }}">New Arrivals</a></li>
                            </ul>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <a href="{{ route('store.register') }}" class="btn btn-marketplace-secondary d-none d-lg-inline-flex">
                            Open a Store
                        </a>
                        <a class="btn btn-outline-dark" href="{{ route('cart.index') }}">
                            <i class="bi-cart-fill me-1"></i>
                            Cart
                            <span class="badge bg-dark text-white ms-1 rounded-pill" id="cart-count">{{ $cartCount }}</span>
                        </a>
                        @if($currentUser)
                            <div class="dropdown">
                                <a class="btn btn-marketplace-primary dropdown-toggle d-flex align-items-center" href="#" role="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle me-2"></i>
                                    {{ $currentUser->name }}
                                </a>

                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                    @if($currentGuard === 'store')
                                        <li><a class="dropdown-item" href="{{ route('store.profile') }}">My Profile</a></li>
                                        <li><a class="dropdown-item" href="{{ route('store.orders') }}">My Orders</a></li>
                                    @else
                                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">My Profile</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ $currentGuard === 'store' ? route('store.logout') : route('logout') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item text-danger">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-marketplace-primary d-flex align-items-center">
                                <i class="bi bi-person-circle fs-5"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <main class="flex-fill">
            @if(session('success'))
                <div class="container mt-3">
                    <div class="alert alert-success marketplace-alert">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="container mt-3">
                    <div class="alert alert-danger marketplace-alert">{{ session('error') }}</div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="marketplace-footer pt-5 pb-3">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4" id="marketplace-about">
                        <h5 class="fw-bold">Multistore Marketplace</h5>
                        <p class="text-muted">
                            A multi-store ecommerce platform where customers can browse trusted stores and vendors can grow from one shared marketplace.
                        </p>
                    </div>

                    <div class="col-md-4 mb-4">
                        <h5 class="fw-bold">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('frontend.home') }}" class="text-decoration-none text-light">Home</a></li>
                            <li><a href="{{ route('frontend.stores.index') }}" class="text-decoration-none text-light">Stores</a></li>
                            <li><a href="{{ route('frontend.products.index') }}" class="text-decoration-none text-light">Products</a></li>
                            <li>
                                <a href="{{ route('store.register') }}" class="text-warning fw-bold text-decoration-none">
                                    Register Your Store
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4 mb-4">
                        <h5 class="fw-bold">Contact</h5>
                        <p class="mb-1">Email: support@multistore.test</p>
                        <p class="mb-1">Phone: +966 000000000</p>

                        <div class="mt-3">
                            <a href="#" class="text-light me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-light me-3"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-light"><i class="bi bi-twitter"></i></a>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary">

                <div class="text-center">
                    <p class="mb-0">&copy; 2026 Multistore Marketplace. All Rights Reserved.</p>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="{{ asset('js/scripts.js') }}"></script>
    </body>
</html>
