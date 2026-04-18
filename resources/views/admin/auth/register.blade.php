<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Main Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ✅ Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            min-height: 100vh;
        }
        .register-card {
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="row w-100 justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">

            <div class="card shadow-lg register-card">
                <div class="card-header text-center bg-dark text-white py-4">
                    <h4 class="mb-0">Create Main Admin</h4>
                </div>

                <div class="card-body p-4">

                    {{-- ✅ Error message --}}
                    @if($errors->any())
                        <div class="alert alert-danger text-center">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    {{-- ✅ Success message --}}
                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.register.post') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Full Name In Arabic</label>
                            <input 
                                type="text" 
                                name="name_ar" 
                                class="form-control"
                                placeholder="Enter full name"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name In English</label>
                            <input 
                                type="text" 
                                name="name_en" 
                                class="form-control"
                                placeholder="Enter full name"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address In Arabic</label>
                            <input 
                                type="text" 
                                name="address_ar" 
                                class="form-control"
                                placeholder="Enter address"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address In English</label>
                            <input 
                                type="text" 
                                name="address_en" 
                                class="form-control"
                                placeholder="Enter address"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control"
                                placeholder="Enter email"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control"
                                placeholder="Enter password"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                class="form-control"
                                placeholder="Confirm password"
                                required
                            >
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                Create Admin
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <span class="text-muted">Already have an account?</span>
                            <a href="{{ route('admin.auth.login') }}" class="fw-bold text-decoration-none">
                                Login Here
                            </a>
                        </div>


                    </form>
                </div>

                <div class="card-footer text-center text-muted py-3">
                    &copy; {{ date('Y') }} Admin Management
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
