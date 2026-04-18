@extends('frontend.layouts.app')

@section('content')
<div class="container py-5" style="max-width: 600px;">
    <h3 class="mb-4 text-center">Create Account</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('store.register.post') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Store Name (EN)</label>
                <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}" required>
            </div>
            <div class="col-md-6">
                <label>Store Name (AR)</label>
                <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Description (EN)</label>
                <textarea type="text" name="desc_en" class="form-control" value="{{ old('desc_en') }}" required></textarea>
            </div>
            <div class="col-md-6">
                <label>Description (AR)</label>
                <textarea type="text" name="desc_ar" class="form-control" value="{{ old('desc_ar') }}" required></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Category</label>
                <select name="store_category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    @foreach($storecategories as $storecategorie)
                        <option value="{{ $storecategorie->id }}" 
    {{ old('store_category_id') == $storecategorie->id ? 'selected' : '' }}>{{ $storecategorie->translation->store_category }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Phone (add code)</label>
                <input type="tel" name="phone" class="form-control" placeholder="+974 XXXXXXXX" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control" value="{{ old('logo') }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Address (EN)</label>
                <textarea type="text" name="addr_en" class="form-control" value="{{ old('addr_en') }}" required></textarea>
            </div>
            <div class="col-md-6">
                <label>Address (AR)</label>
                <textarea type="text" name="addr_ar" class="form-control" value="{{ old('addr_ar') }}" required></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Authentication Email</label>
                <input type="email" name="auth_email" class="form-control" value="{{ old('auth_email') }}" required>
            </div>
            <div class="col-md-4">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100">Register</button>

        <p class="text-center mt-3">
            Already have an account?
            <a href="{{ route('store.login') }}">Login</a>
        </p>
    </form>
</div>
@endsection
