<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ✅ Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="row w-100 justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-10">

            <div class="card shadow-lg login-card">
                <div class="card-header text-center bg-dark text-white py-4">
                    <h4 class="mb-0">Admin Panel Login</h4>
                </div>

                <div class="card-body p-4">

                    {{-- ✅ Error Message --}}
                    @if($errors->any())
                        <div class="alert alert-danger text-center">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control"
                                placeholder="Enter your email"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control"
                                placeholder="Enter your password"
                                required
                            >
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Login
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <span class="text-muted">Don't have an admin account?</span>
                            <a href="{{ route('admin.auth.register') }}" class="fw-bold text-decoration-none">
                                Create Admin
                            </a>
                        </div>


                    </form>
                </div>

                <div class="card-footer text-center text-muted py-3">
                    &copy; {{ date('Y') }} Admin Panel
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
