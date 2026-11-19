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

class UserController extends Controller
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
            ->with('permissions')
            ->withCount('permissions')
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

        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'flatPermissions' => PermissionCatalog::flatPermissions(),
            'search' => $search,
            'roleFilter' => $roleFilter,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->filled('roles') && $request->filled('role')) {
            $request->merge(['roles' => [(string) $request->input('role')]]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan dan dikaitkan ke grup permission.');
    }

    public function show(string $id)
    {
        return redirect()->route('users.index');
    }

    public function edit(string $id)
    {
        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if (! $request->filled('roles') && $request->filled('role')) {
            $request->merge(['roles' => [(string) $request->input('role')]]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'exists:roles,name'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = bcrypt($validated['password']);
        }

        $user->update($payload);
        $user->syncRoles($validated['roles']);

        if ($request->input('_return_to') === 'permission-groups') {
            return redirect()
                ->route('permission-groups.index')
                ->with('success', "Grup permission untuk {$user->name} berhasil diperbarui.");
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna dan grup permission berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return redirect()
                ->route('users.index')
                ->withErrors(['user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function search(Request $request): View
    {
        return $this->index($request);
    }
}
