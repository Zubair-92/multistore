@extends('admin.layouts.app')

@section('content')
<h2>Add Category</h2>

<form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
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
        <input type="file" name="logo" class="form-control text-end" dir="rtl" required>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
</form>
@endsection
