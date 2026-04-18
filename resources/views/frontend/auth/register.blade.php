@extends('frontend.layouts.app')

@section('content')
<div class="container py-5" style="max-width: 500px;">
    <h3 class="mb-4 text-center">Create Account</h3>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <button class="btn btn-success w-100">Register</button>

        <p class="text-center mt-3">
            Already have an account?
            <a href="{{ route('login') }}">Login</a>
        </p>
    </form>
</div>
@endsection
