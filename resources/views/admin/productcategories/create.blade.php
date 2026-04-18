@extends('admin.layouts.app')

@section('content')
<h2>Add Product Category</h2>

<form action="{{ route('admin.productcategories.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label class="form-label">Category</label>
        <select id="category_id" name="category_id" class="form-select" required>
            <option value="">Select Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">
                    {{ optional($category->translation)->category }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Sub Category</label>
        <select id="sub_category_id" name="sub_category_id" class="form-select" required>
            <option value="">Select SubCategory</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Name (English)</label>
        <input type="text" name="name_en" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Name (Arabic)</label>
        <input type="text" name="name_ar" class="form-control text-end" dir="rtl" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Logo</label>
        <input type="file" name="logo" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Save</button>
</form>
@endsection
