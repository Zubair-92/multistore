@extends('frontend.layouts.app')

@section('content')
<div class="container py-5" style="max-width: 500px;">
    <h3 class="mb-4 text-center">Login</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('store.login.post') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">Login</button>

        <p class="text-center mt-3">
            Don't have an account?
            <a href="{{ route('store.register') }}">Register</a>
        </p>
    </form>
</div>
@endsection
