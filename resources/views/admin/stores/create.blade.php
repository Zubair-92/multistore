@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Add New Store</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Fix the following errors:<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.stores.store') }}" method="POST" enctype="multipart/form-data">
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
                <textarea name="description_en" class="form-control" value="{{ old('description') }}" required></textarea>
            </div>
            <div class="col-md-6">
                <label>Description (AR)</label>
                <textarea  name="description_ar" class="form-control" value="{{ old('description') }}" required></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Store Category</label>
                <select name="store_category_id" class="form-select" required>
                    <option value="">Select Store Category</option>
                    @foreach($storecategories as $storecat)
                        <option value="{{ $storecat->id }}">{{ optional($storecat->translation)->store_category }}</option>
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
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control text-end" dir="rtl" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Address En</label>
                <textarea name="address_en" class="form-control"  required></textarea>
            </div>
            <div class="col-md-6">
                <label>Address Ar</label>
                <textarea name="address_ar" class="form-control"  required></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Authentication Email</label>
                <input type="text" name="auth_email" class="form-control"  required>
            </div>
            <div class="col-md-4">
                <label>Password</label>
                <input type="password" name="password" class="form-control"  required>
            </div>
            <div class="col-md-4">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control text-end" dir="rtl" required>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="approved" value="1">
            <label class="form-check-label">Approved</label>
        </div>

        <button type="submit" class="btn btn-success">Add Store</button>
        <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
