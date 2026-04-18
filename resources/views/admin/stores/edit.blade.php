@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Edit Store</h2>

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

    <form action="{{ route('admin.stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- STORE NAME --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Store Name (EN)</label>
                <input type="text" name="name_en" class="form-control"
                       value="{{ optional($store->translations->where('locale','en')->first())->name }}" required>
            </div>

            <div class="col-md-6">
                <label>Store Name (AR)</label>
                <input type="text" name="name_ar" class="form-control"
                       value="{{ optional($store->translations->where('locale','ar')->first())->name }}" required>
            </div>
        </div>

        {{-- DESCRIPTION --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Description (EN)</label>
                <textarea name="description_en" class="form-control" required>{{ optional($store->translations->where('locale','en')->first())->description }}</textarea>
            </div>

            <div class="col-md-6">
                <label>Description (AR)</label>
                <textarea name="description_ar" class="form-control" required>{{ optional($store->translations->where('locale','ar')->first())->description }}</textarea>
            </div>
        </div>

        {{-- CATEGORY + EMAIL --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Store Category</label>
                <select name="store_category_id" class="form-select" required>
                    <option value="">Select Store Category</option>

                    @foreach($storecategories as $storecat)
                        <option value="{{ $storecat->id }}"
                            {{ $store->store_category_id == $storecat->id ? 'selected' : '' }}>
                            {{ optional($storecat->translation)->store_category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ $store->email }}" required>
            </div>
        </div>

        {{-- PHONE + LOGO --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ $store->phone }}" required>
            </div>

            <div class="col-md-6">
                <label>Logo</label>
                <input type="file" name="logo" class="form-control">
                @if($store->logo)
                    <img src="{{ asset('storage/'.$store->logo) }}" width="80" class="mt-2 rounded border">
                @endif
            </div>
        </div>

        {{-- APPROVED --}}
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="approved" value="1"
                {{ $store->approved ? 'checked' : '' }}>
            <label class="form-check-label">Approved</label>
        </div>

        <button type="submit" class="btn btn-warning">Update Store</button>
        <a href="{{ route('admin.stores.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
