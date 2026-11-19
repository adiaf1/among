<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionGroupController extends Controller
{
    public function index(Request $request): View
    {
        if (Permission::query()->count() < count(PermissionCatalog::allPermissionNames())) {
            PermissionCatalog::syncDefaultPermissionsAndRoles();
        }

        $search = $request->string('search')->toString();
        $roleFilter = $request->string('role')->toString();
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $roles = Role::query()
            ->with(['permissions', 'users'])
            ->withCount(['users', 'permissions'])
            ->orderByRaw("CASE WHEN name = 'super-admin' THEN 1 WHEN name = 'admin' THEN 2 WHEN name = 'editor' THEN 3 WHEN name = 'guest' THEN 4 ELSE 5 END")
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->with('roles.permissions')
            ->when($roleFilter, function ($query) use ($roleFilter) {
                $query->whereHas('roles', fn ($q) => $q->where('name', $roleFilter));
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('permission-groups.index', [
            'roles' => $roles,
            'users' => $users,
            'permissionGroups' => PermissionCatalog::groups(),
            'flatPermissions' => PermissionCatalog::flatPermissions(),
            'protectedRoles' => PermissionCatalog::PROTECTED_ROLES,
            'superAdminRoles' => PermissionCatalog::SUPER_ADMIN_ROLES,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Permission::query()->count() < count(PermissionCatalog::allPermissionNames())) {
            PermissionCatalog::syncDefaultPermissionsAndRoles();
        }

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::allPermissionNames())],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('permission-groups.index')
            ->with('success', "Grup permission \"{$role->name}\" berhasil dibuat.");
    }

    public function update(Request $request, Role $permission_group): RedirectResponse
    {
        if (Permission::query()->count() < count(PermissionCatalog::allPermissionNames())) {
            PermissionCatalog::syncDefaultPermissionsAndRoles();
        }

        if (in_array($permission_group->name, PermissionCatalog::SUPER_ADMIN_ROLES, true)) {
            $permission_group->syncPermissions(PermissionCatalog::allPermissionNames());

            return redirect()
                ->route('permission-groups.index')
                ->with('success', "Grup \"{$permission_group->name}\" memiliki akses penuh Super Admin.");
        }

        $isProtectedName = in_array($permission_group->name, PermissionCatalog::PROTECTED_ROLES, true);

        $request->merge([
            'name' => $isProtectedName ? $permission_group->name : trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($permission_group->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionCatalog::allPermissionNames())],
        ]);

        if (! $isProtectedName) {
            $permission_group->update([
                'name' => $validated['name'],
            ]);
        }

        $permission_group->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('permission-groups.index')
            ->with('success', "Grup permission \"{$permission_group->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $permission_group): RedirectResponse
    {
        if (in_array($permission_group->name, PermissionCatalog::PROTECTED_ROLES, true)) {
            return redirect()
                ->route('permission-groups.index')
                ->withErrors(['role' => "Grup sistem \"{$permission_group->name}\" tidak dapat dihapus."]);
        }

        $name = $permission_group->name;
        $permission_group->delete();

        return redirect()
            ->route('permission-groups.index')
            ->with('success', "Grup permission \"{$name}\" berhasil dihapus.");
    }
}
