<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" class="form-control @error('code') is-invalid @enderror" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Type</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach(\App\Models\Coupon::TYPES as $type)
                <option value="{{ $type }}" @selected(old('type', $coupon->type ?? 'fixed') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Value</label>
        <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value ?? '') }}" class="form-control @error('value') is-invalid @enderror" required>
        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Minimum Cart Amount</label>
        <input type="number" step="0.01" name="minimum_amount" value="{{ old('minimum_amount', $coupon->minimum_amount ?? 0) }}" class="form-control @error('minimum_amount') is-invalid @enderror">
        @error('minimum_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input type="date" name="starts_at" value="{{ old('starts_at', isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d') : '') }}" class="form-control @error('starts_at') is-invalid @enderror">
        @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="expires_at" value="{{ old('expires_at', isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '') }}" class="form-control @error('expires_at') is-invalid @enderror">
        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Usage Limit</label>
        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" class="form-control @error('usage_limit') is-invalid @enderror">
        @error('usage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" @checked(old('is_active', $coupon->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Back</a>
        <button class="btn btn-primary">Save Coupon</button>
    </div>
</div>
