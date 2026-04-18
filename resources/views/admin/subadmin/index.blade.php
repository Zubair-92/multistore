@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Subadmins</h2>
            <p class="text-muted mb-0">Create limited-access admin accounts for operations and support work.</p>
        </div>
        <a href="{{ route('admin.subadmins.create') }}" class="btn btn-primary">Add Subadmin</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Permissions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subadmins as $subadmin)
                        <tr>
                            <td>{{ $subadmin->name ?? 'Subadmin #'.$subadmin->id }}</td>
                            <td>{{ $subadmin->email }}</td>
                            <td>
                                <span class="badge bg-{{ $subadmin->is_active ? 'success' : 'secondary' }}">
                                    {{ $subadmin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                {{ collect($subadmin->admin_permissions ?? [])->map(fn ($permission) => $availablePermissions[$permission] ?? $permission)->join(', ') ?: 'No permissions assigned' }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.subadmins.edit', $subadmin) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.subadmins.destroy', $subadmin) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this subadmin?')" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No subadmins created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($subadmins->hasPages())
        <div class="mt-4">
            {{ $subadmins->links() }}
        </div>
    @endif
</div>
@endsection
