@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Edit Subadmin</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('admin.subadmins.update', $subadmin) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $subadmin->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $subadmin->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $subadmin->address) }}">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">Permissions</label>
                        <div class="row g-2">
                            @php($selectedPermissions = old('admin_permissions', $subadmin->admin_permissions ?? []))
                            @foreach($availablePermissions as $permission => $label)
                                <div class="col-md-6">
                                    <div class="form-check border rounded p-3 h-100">
                                        <input class="form-check-input" type="checkbox" name="admin_permissions[]" value="{{ $permission }}" id="perm_{{ $permission }}" @checked(in_array($permission, $selectedPermissions))>
                                        <label class="form-check-label fw-semibold" for="perm_{{ $permission }}">{{ $label }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="is_active" @checked(old('is_active', $subadmin->is_active))>
                            <label class="form-check-label" for="is_active">Active account</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.subadmins.index') }}" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Update Subadmin</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
