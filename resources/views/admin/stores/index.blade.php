@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Stores</h2>

    <div id="alertBox"></div>

    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
        <a href="{{ route('admin.stores.create') }}" class="btn btn-primary">Add New Store</a>

        <div class="d-flex gap-2 flex-wrap">
            <select id="bulkAction" class="form-select">
                <option value="">Bulk Action</option>
                <option value="approve">Approve</option>
                <option value="unapprove">Unapprove</option>
                <option value="delete">Delete</option>
            </select>

            <button id="applyBulk" class="btn btn-dark">Apply</button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Logo</th>
                        <th>Store Name (EN)</th>
                        <th>Store Name (AR)</th>
                        <th>Store Category</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Approved</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $store)
                        <tr id="row-{{ $store->id }}">
                            <td><input type="checkbox" class="store-checkbox" value="{{ $store->id }}"></td>
                            <td>{{ $stores->firstItem() + $loop->index }}</td>
                            <td>
                                @if($store->logo)
                                    <img src="{{ asset('storage/'.$store->logo) }}" width="50" alt="Store logo">
                                @endif
                            </td>
                            <td>{{ optional($store->translations->where('locale', 'en')->first())->name }}</td>
                            <td>{{ optional($store->translations->where('locale', 'ar')->first())->name }}</td>
                            <td>{{ optional($store->storeCategory->translation)->store_category ?? '-' }}</td>
                            <td>{{ $store->email }}</td>
                            <td>{{ $store->phone }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-approve" type="checkbox" data-id="{{ $store->id }}" {{ $store->approved ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.stores.edit', $store->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $store->id }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No stores found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($stores->hasPages())
        <div class="mt-4">
            {{ $stores->links() }}
        </div>
    @endif
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function showAlert(message) {
    const alertBox = document.getElementById('alertBox');
    alertBox.innerHTML = `<div class="alert alert-success">${message}</div>`;

    setTimeout(() => {
        alertBox.innerHTML = '';
    }, 2000);
}

async function adminPost(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    if (response.status === 419) {
        showAlert('Your session expired. Refreshing the page...');
        setTimeout(() => window.location.reload(), 1200);
        throw new Error('session-expired');
    }

    if (! response.ok) {
        throw new Error('request-failed');
    }

    return response.json();
}

document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.store-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

document.querySelectorAll('.toggle-approve').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const checkbox = this;

        adminPost("{{ route('admin.stores.toggleApprove') }}", { id: this.dataset.id })
            .then(data => {
                if (data.status) {
                    showAlert('Status updated');
                    setTimeout(() => location.reload(), 1000);
                    return;
                }

                checkbox.checked = !checkbox.checked;
                alert('Failed to update status');
            })
            .catch((error) => {
                if (error.message === 'session-expired') {
                    return;
                }

                checkbox.checked = !checkbox.checked;
                alert('Something went wrong');
            });
    });
});

document.getElementById('applyBulk').addEventListener('click', function() {
    const action = document.getElementById('bulkAction').value;
    const selected = Array.from(document.querySelectorAll('.store-checkbox:checked')).map(cb => cb.value);

    if (! action) return alert('Select action');
    if (selected.length === 0) return alert('Select at least one store');
    if (action === 'delete' && ! confirm('Delete selected stores?')) return;

    adminPost("{{ route('admin.stores.bulkAction') }}", { action, ids: selected })
        .then(data => {
            if (! data.status) return alert('Bulk action failed');

            showAlert('Bulk action completed');
            setTimeout(() => location.reload(), 1000);
        })
        .catch((error) => {
            if (error.message !== 'session-expired') {
                alert('Something went wrong');
            }
        });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (! confirm('Delete this store?')) return;

        const id = this.dataset.id;

        adminPost("{{ route('admin.stores.bulkAction') }}", { action: 'delete', ids: [id] })
            .then(data => {
                if (data.status) {
                    showAlert('Deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                    return;
                }

                alert('Failed to delete store');
            })
            .catch((error) => {
                if (error.message !== 'session-expired') {
                    alert('Something went wrong');
                }
            });
    });
});
</script>
@endsection
