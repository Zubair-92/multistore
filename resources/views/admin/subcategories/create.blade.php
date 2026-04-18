@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Add Subcategory</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ✅ enctype REQUIRED for file upload --}}
    <form action="{{ route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ optional($category->translation)->category }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ LOGO FIELD --}}
        <div class="mb-3">
            <label class="form-label">Subcategory Logo</label>
            <input type="file" name="logo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subcategory Name (English)</label>
            <input type="text" name="name_en" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subcategory Name (Arabic)</label>
            <input type="text" name="name_ar" class="form-control text-end" dir="rtl" required>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
