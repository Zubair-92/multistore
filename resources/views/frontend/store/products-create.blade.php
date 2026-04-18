@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add Product</h2>
            <p class="text-muted mb-0">Create a new product for your store catalog.</p>
        </div>
        <a href="{{ route('store.products') }}" class="btn btn-outline-secondary">Back to Products</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('store.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->translation->category ?? 'No Name' }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror">
                            <option value="">Select Subcategory</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" @selected(old('subcategory_id') == $subcategory->id)>
                                    {{ $subcategory->translation->sub_category ?? 'No Name' }}
                                </option>
                            @endforeach
                        </select>
                        @error('subcategory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Category</label>
                        <select name="product_category_id" class="form-select @error('product_category_id') is-invalid @enderror">
                            <option value="">Select Product Category</option>
                            @foreach ($productCategories as $pc)
                                <option value="{{ $pc->id }}" @selected(old('product_category_id') == $pc->id)>
                                    {{ $pc->translation->product_category ?? 'No Name' }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="price" value="{{ old('price') }}" class="form-control @error('price') is-invalid @enderror" step="0.01" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Offer Price</label>
                        <input type="number" name="offer_price" value="{{ old('offer_price') }}" class="form-control @error('offer_price') is-invalid @enderror" step="0.01">
                        @error('offer_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" class="form-control @error('stock') is-invalid @enderror" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" @selected(old('status', '1') == '1')>Active</option>
                            <option value="0" @selected(old('status') == '0')>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Name (EN)</label>
                        <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control @error('name_en') is-invalid @enderror" required>
                        @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Name (AR)</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar') }}" class="form-control @error('name_ar') is-invalid @enderror" required>
                        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description (EN)</label>
                        <textarea name="description_en" rows="4" class="form-control @error('description_en') is-invalid @enderror">{{ old('description_en') }}</textarea>
                        @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description (AR)</label>
                        <textarea name="description_ar" rows="4" class="form-control @error('description_ar') is-invalid @enderror">{{ old('description_ar') }}</textarea>
                        @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Save Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
