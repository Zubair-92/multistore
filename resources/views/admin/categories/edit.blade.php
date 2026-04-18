@extends('admin.layouts.app')

@section('content')
<h2>Edit Category</h2>

<form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Name (English)</label>
        <input type="text" name="name_en" class="form-control"
            value="{{ optional($category->translations->where('locale','en')->first())->category }}"
            required>
    </div>

    <div class="mb-3">
        <label class="form-label">Name (Arabic)</label>
        <input type="text" name="name_ar" class="form-control text-end" dir="rtl"
            value="{{ optional($category->translations->where('locale','ar')->first())->category }}"
            required>
    </div>

    <div class="mb-3">
        <label class="form-label">Logo</label>
        <input type="file" name="logo" class="form-control">

        @if($category->logo)
            <img src="{{ asset('storage/'.$category->logo) }}" width="80" class="mt-2">
        @endif
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection
