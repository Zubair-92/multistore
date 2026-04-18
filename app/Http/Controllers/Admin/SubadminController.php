<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SubadminController extends Controller
{
    private const AVAILABLE_PERMISSIONS = [
        'catalog' => 'Catalog Management',
        'stores' => 'Store Management',
        'products' => 'Product Management',
        'orders' => 'Order Management',
        'subadmins' => 'Subadmin Management',
    ];

    public function index()
    {
        $subadmins = User::query()
            ->with(['role', 'translations'])
            ->whereHas('role', fn ($query) => $query->where('name', 'sub_admin'))
            ->latest('id')
            ->paginate(12);

        return view('admin.subadmin.index', [
            'subadmins' => $subadmins,
            'availablePermissions' => self::AVAILABLE_PERMISSIONS,
        ]);
    }

    public function create()
    {
        return view('admin.subadmin.create', [
            'availablePermissions' => self::AVAILABLE_PERMISSIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'admin_permissions' => ['nullable', 'array'],
            'admin_permissions.*' => ['string', Rule::in(array_keys(self::AVAILABLE_PERMISSIONS))],
        ]);

        $roleId = Role::query()->where('name', 'sub_admin')->value('id');

        $subadmin = User::create([
            'role_id' => $roleId,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'admin_permissions' => array_values($validated['admin_permissions'] ?? []),
        ]);

        $subadmin->syncTranslation([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ], 'en');

        return redirect()->route('admin.subadmins.index')
            ->with('success', 'Subadmin created successfully.');
    }

    public function edit(User $subadmin)
    {
        abort_unless($subadmin->isSubAdmin(), 404);

        return view('admin.subadmin.edit', [
            'subadmin' => $subadmin->load('translations'),
            'availablePermissions' => self::AVAILABLE_PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $subadmin)
    {
        abort_unless($subadmin->isSubAdmin(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subadmin->id)],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'admin_permissions' => ['nullable', 'array'],
            'admin_permissions.*' => ['string', Rule::in(array_keys(self::AVAILABLE_PERMISSIONS))],
        ]);

        $subadmin->update([
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active', true),
            'admin_permissions' => array_values($validated['admin_permissions'] ?? []),
            'password' => filled($validated['password'] ?? null)
                ? Hash::make($validated['password'])
                : $subadmin->password,
        ]);

        $subadmin->syncTranslation([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ], 'en');

        return redirect()->route('admin.subadmins.index')
            ->with('success', 'Subadmin updated successfully.');
    }

    public function destroy(User $subadmin)
    {
        abort_unless($subadmin->isSubAdmin(), 404);

        $subadmin->translations()->delete();
        $subadmin->delete();

        return redirect()->route('admin.subadmins.index')
            ->with('success', 'Subadmin deleted successfully.');
    }
}
