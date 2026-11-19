@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Grup Permission & Hak Akses</h4>
            <p class="text-muted mb-0">Kelola grup permission (role) dan kaitkan hak akses modul ke setiap pengguna. Hanya dapat diakses oleh Super Admin.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                <i class="icon-base bx bx-user me-1"></i> Kelola Pengguna
            </a>
            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#permissionGroupModal"
                data-mode="create"
            >
                <i class="icon-base bx bx-plus me-1"></i> Tambah Grup Permission
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-medium mb-1">Periksa kembali isian berikut:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $totalPermissionsCount = count($flatPermissions);
    @endphp

    <!-- Role / Permission Group Cards (Sneat app-access-roles style) -->
    <div class="row g-4 mb-6">
        @foreach($roles as $role)
            @php
                $isSuperRole = in_array($role->name, $superAdminRoles, true);
                $isProtected = in_array($role->name, $protectedRoles, true);
                $rolePermissionNames = $isSuperRole
                    ? array_keys($flatPermissions)
                    : $role->permissions->pluck('name')->values()->all();
                $roleRecord = [
                    'id' => $role->id,
                    'name' => $role->name,
                    'is_protected' => $isProtected,
                    'is_super' => $isSuperRole,
                    'permissions' => $rolePermissionNames,
                    'update_url' => route('permission-groups.update', $role),
                ];
            @endphp
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small">Total {{ $role->users_count }} pengguna</span>
                                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                    @foreach($role->users->take(4) as $roleUser)
                                        @php
                                            $initials = collect(explode(' ', trim($roleUser->name)))
                                                ->filter()
                                                ->take(2)
                                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                                                ->join('');
                                        @endphp
                                        <li class="avatar avatar-sm pull-up" title="{{ $roleUser->name }}">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ $initials ?: 'U' }}</span>
                                        </li>
                                    @endforeach
                                    @if($role->users_count > 4)
                                        <li class="avatar avatar-sm">
                                            <span class="avatar-initial rounded-circle bg-label-secondary">+{{ $role->users_count - 4 }}</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1 text-capitalize">{{ str_replace('-', ' ', $role->name) }}</h5>
                                    <code class="small text-muted">{{ $role->name }}</code>
                                </div>
                                @if($isSuperRole)
                                    <span class="badge bg-label-danger">Akses Penuh (Super Admin)</span>
                                @else
                                    <span class="badge bg-label-primary">{{ count($rolePermissionNames) }} / {{ $totalPermissionsCount }} Modul</span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-1 my-3">
                                @if($isSuperRole)
                                    <span class="badge bg-label-success">Seluruh Modul & Manajemen Permission</span>
                                @elseif(count($rolePermissionNames) === 0)
                                    <span class="text-muted small">Belum ada hak akses modul yang dipilih</span>
                                @else
                                    @foreach(array_slice($rolePermissionNames, 0, 4) as $permName)
                                        <span class="badge bg-label-info">{{ $flatPermissions[$permName]['label'] ?? $permName }}</span>
                                    @endforeach
                                    @if(count($rolePermissionNames) > 4)
                                        <span class="badge bg-label-secondary">+{{ count($rolePermissionNames) - 4 }} lainnya</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#permissionGroupModal"
                                data-mode="edit"
                                data-record="{{ json_encode($roleRecord) }}"
                            >
                                <i class="icon-base bx bx-edit-alt me-1"></i>
                                {{ $isSuperRole ? 'Lihat Hak Akses' : 'Atur Hak Akses' }}
                            </button>

                            @if(! $isProtected)
                                <form
                                    action="{{ route('permission-groups.destroy', $role) }}"
                                    method="POST"
                                    class="d-inline"
                                    data-confirm-delete
                                    data-confirm-title="Hapus Grup Permission?"
                                    data-confirm-text="Grup permission {{ $role->name }} akan dihapus dan dilepas dari pengguna terkait."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Grup">
                                        <span class="icon-base bx bx-trash icon-sm"></span>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-label-secondary">Grup Sistem</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Add New Role Card -->
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="row h-100 g-0">
                    <div class="col-sm-5 d-flex align-items-end justify-content-center pt-4 ps-3">
                        <img
                            src="{{ asset('assets/img/illustrations/lady-with-laptop-light.png') }}"
                            class="img-fluid"
                            alt="Tambah Grup Permission"
                            width="115"
                        >
                    </div>
                    <div class="col-sm-7">
                        <div class="card-body text-sm-end text-center d-flex flex-column justify-content-center h-100">
                            <div class="mb-3">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary text-nowrap"
                                    data-bs-toggle="modal"
                                    data-bs-target="#permissionGroupModal"
                                    data-mode="create"
                                >
                                    <i class="icon-base bx bx-plus me-1"></i> Tambah Grup Baru
                                </button>
                            </div>
                            <p class="mb-0 text-muted small">
                                Buat grup permission baru dan pilih hak akses modul sesuai tugas pengguna.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users & Linked Permission Groups Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h5 class="mb-0">Keterkaitan Pengguna & Grup Permission</h5>
                    <small class="text-muted">Klik tombol <strong>Kaitkan Grup</strong> pada baris pengguna untuk mengubah grup permission yang dimiliki.</small>
                </div>
            </div>
            <form method="GET" action="{{ route('permission-groups.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-4 col-lg-3">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Grup Permission</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->name }}" @selected($roleFilter === $roleOption->name)>
                                {{ $roleOption->name }} ({{ $roleOption->users_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Cari nama atau email pengguna lalu tekan Enter..."
                    >
                </div>
                @if($search || $roleFilter)
                    <div class="col-md-auto">
                        <a href="{{ route('permission-groups.index') }}" class="btn btn-label-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Grup Permission Terkait</th>
                        <th>Hak Akses Aktif</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($users as $user)
                        @php
                            $userRoleNames = $user->roles->pluck('name')->values()->all();
                            $userIsSuper = $user->isSuperAdmin();
                            $userPermissions = $userIsSuper
                                ? array_keys($flatPermissions)
                                : $user->getAllPermissions()->pluck('name')->values()->all();
                            $userRecord = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'roles' => $userRoleNames,
                                'update_url' => route('users.update', $user),
                            ];
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $user->name }}</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse($user->roles as $userRole)
                                    @php
                                        $roleBadge = in_array($userRole->name, $superAdminRoles, true)
                                            ? 'bg-label-danger'
                                            : ($userRole->name === 'editor' ? 'bg-label-primary' : 'bg-label-info');
                                    @endphp
                                    <span class="badge {{ $roleBadge }} me-1">{{ $userRole->name }}</span>
                                @empty
                                    <span class="text-muted small">Belum ada grup</span>
                                @endforelse
                            </td>
                            <td>
                                @if($userIsSuper)
                                    <span class="badge bg-label-success">Semua Modul (Super Admin)</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ count($userPermissions) }} dari {{ $totalPermissionsCount }} modul</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="offcanvas"
                                    data-bs-target="#assignUserRoleOffcanvas"
                                    data-user-record="{{ json_encode($userRecord) }}"
                                >
                                    <i class="icon-base bx bx-link-alt me-1"></i> Kaitkan Grup
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Data pengguna tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('permission-groups.index') }}" class="d-flex align-items-center gap-2">
                    @if($roleFilter)
                        <input type="hidden" name="role" value="{{ $roleFilter }}">
                    @endif
                    @if($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    <label for="per_page" class="text-muted small mb-0">Tampilkan</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
                <span class="text-muted small">
                    Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
                </span>
            </div>
            <div>
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Grup Permission (Sneat Add Role Modal style) -->
<div class="modal fade" id="permissionGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title mb-0" id="permissionGroupModalTitle">Tambah Grup Permission</h5>
                    <small class="text-muted">Atur nama grup dan pilih modul yang diizinkan untuk diakses.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="permissionGroupForm" method="POST" action="{{ route('permission-groups.store') }}">
                @csrf
                <input type="hidden" name="_method" id="permissionGroupMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label" for="permissionGroupName">Nama Grup Permission <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="permissionGroupName"
                            name="name"
                            class="form-control"
                            placeholder="Contoh: kepala-gudang, bagian-penjualan, tim-produksi"
                            required
                        >
                        <div class="form-text" id="permissionGroupNameHint">Gunakan nama grup yang jelas sesuai bagian/fungsi pengguna.</div>
                    </div>

                    <div class="alert alert-info d-none" id="superAdminRoleAlert">
                        Grup <strong>Super Admin / Admin</strong> memiliki akses penuh ke seluruh modul sistem dan tidak dapat dibatasi.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Daftar Hak Akses Modul</h6>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                            <label class="form-check-label fw-medium" for="selectAllPermissions">Pilih Semua Modul</label>
                        </div>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-flush-spacing align-middle mb-0">
                            <tbody>
                                @foreach($permissionGroups as $groupLabel => $groupItems)
                                    <tr class="table-light">
                                        <td colspan="2" class="fw-semibold text-heading py-2">
                                            <i class="icon-base bx bx-folder-open me-1 text-primary"></i> {{ $groupLabel }}
                                        </td>
                                    </tr>
                                    @foreach($groupItems as $permKey => $permLabel)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-medium text-heading">{{ $permLabel }}</div>
                                                <code class="small text-muted">{{ $permKey }}</code>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="form-check form-switch d-inline-block mb-0">
                                                    <input
                                                        class="form-check-input permission-checkbox"
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permKey }}"
                                                        id="perm_{{ md5($permKey) }}"
                                                    >
                                                    <label class="form-check-label" for="perm_{{ md5($permKey) }}">Izinkan</label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="permissionGroupSubmitBtn">
                        <i class="icon-base bx bx-save me-1"></i> Simpan Grup Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Offcanvas Kaitkan Grup Permission ke User -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="assignUserRoleOffcanvas" aria-labelledby="assignUserRoleOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="assignUserRoleOffcanvasLabel" class="offcanvas-title">Kaitkan Grup Permission</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="assignUserRoleForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="_return_to" value="permission-groups">
            <input type="hidden" name="name" id="assignUserNameInput">
            <input type="hidden" name="email" id="assignUserEmailInput">

            <div class="mb-4 p-3 rounded border bg-light-subtle">
                <div class="fw-semibold text-heading" id="assignUserDisplayName">-</div>
                <div class="text-muted small" id="assignUserDisplayEmail">-</div>
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Pilih Grup Permission <span class="text-danger">*</span></label>
                <div class="d-flex flex-column gap-2">
                    @foreach($roles as $roleItem)
                        @php
                            $isSuperItem = in_array($roleItem->name, $superAdminRoles, true);
                            $permCount = $isSuperItem ? $totalPermissionsCount : $roleItem->permissions_count;
                        @endphp
                        <label class="form-check custom-option-basic p-3 border rounded d-flex align-items-start gap-2 cursor-pointer" for="user_role_{{ $roleItem->id }}">
                            <input
                                class="form-check-input mt-1 user-role-checkbox"
                                type="checkbox"
                                name="roles[]"
                                value="{{ $roleItem->name }}"
                                id="user_role_{{ $roleItem->id }}"
                            >
                            <span class="d-block">
                                <span class="fw-medium d-block">{{ $roleItem->name }}</span>
                                <small class="text-muted">
                                    {{ $isSuperItem ? 'Akses Penuh Seluruh Modul & Super Admin' : "{$permCount} modul diizinkan" }}
                                </small>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Grup Pengguna</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('permissionGroupModal');
        const formEl = document.getElementById('permissionGroupForm');
        const methodInput = document.getElementById('permissionGroupMethod');
        const titleEl = document.getElementById('permissionGroupModalTitle');
        const nameInput = document.getElementById('permissionGroupName');
        const nameHint = document.getElementById('permissionGroupNameHint');
        const superAlert = document.getElementById('superAdminRoleAlert');
        const selectAllCheckbox = document.getElementById('selectAllPermissions');
        const submitBtn = document.getElementById('permissionGroupSubmitBtn');
        const permissionCheckboxes = Array.from(document.querySelectorAll('.permission-checkbox'));
        const storeUrl = @json(route('permission-groups.store'));

        const syncSelectAllState = function () {
            if (!selectAllCheckbox) {
                return;
            }
            const allChecked = permissionCheckboxes.length > 0 && permissionCheckboxes.every(function (cb) {
                return cb.checked;
            });
            selectAllCheckbox.checked = allChecked;
        };

        selectAllCheckbox?.addEventListener('change', function () {
            permissionCheckboxes.forEach(function (cb) {
                if (!cb.disabled) {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
        });

        permissionCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', syncSelectAllState);
        });

        document.querySelectorAll('[data-bs-target="#permissionGroupModal"]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                const mode = trigger.getAttribute('data-mode') || 'create';

                if (mode === 'edit') {
                    const record = JSON.parse(trigger.getAttribute('data-record') || '{}');
                    const selectedSet = new Set(record.permissions || []);
                    const isSuper = Boolean(record.is_super);
                    const isProtected = Boolean(record.is_protected);

                    titleEl.textContent = `Atur Grup Permission: ${record.name || ''}`;
                    formEl.action = record.update_url || storeUrl;
                    methodInput.value = 'PUT';
                    nameInput.value = record.name || '';
                    nameInput.readOnly = isProtected;
                    nameHint.textContent = isProtected
                        ? 'Nama grup sistem bawaan tidak dapat diubah.'
                        : 'Gunakan nama grup yang jelas sesuai bagian/fungsi pengguna.';

                    superAlert.classList.toggle('d-none', !isSuper);
                    selectAllCheckbox.disabled = isSuper;
                    submitBtn.disabled = isSuper;

                    permissionCheckboxes.forEach(function (cb) {
                        cb.checked = selectedSet.has(cb.value);
                        cb.disabled = isSuper;
                    });
                    syncSelectAllState();
                } else {
                    titleEl.textContent = 'Tambah Grup Permission Baru';
                    formEl.action = storeUrl;
                    methodInput.value = 'POST';
                    nameInput.value = '';
                    nameInput.readOnly = false;
                    nameHint.textContent = 'Gunakan nama grup yang jelas sesuai bagian/fungsi pengguna.';
                    superAlert.classList.add('d-none');
                    selectAllCheckbox.disabled = false;
                    selectAllCheckbox.checked = false;
                    submitBtn.disabled = false;

                    permissionCheckboxes.forEach(function (cb) {
                        cb.checked = false;
                        cb.disabled = false;
                    });
                }
            });
        });

        const assignForm = document.getElementById('assignUserRoleForm');
        const assignNameInput = document.getElementById('assignUserNameInput');
        const assignEmailInput = document.getElementById('assignUserEmailInput');
        const assignDisplayName = document.getElementById('assignUserDisplayName');
        const assignDisplayEmail = document.getElementById('assignUserDisplayEmail');
        const userRoleCheckboxes = Array.from(document.querySelectorAll('.user-role-checkbox'));

        document.querySelectorAll('[data-bs-target="#assignUserRoleOffcanvas"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const record = JSON.parse(button.getAttribute('data-user-record') || '{}');
                const roleSet = new Set(record.roles || []);

                if (assignForm) {
                    assignForm.action = record.update_url || '';
                }
                if (assignNameInput) {
                    assignNameInput.value = record.name || '';
                }
                if (assignEmailInput) {
                    assignEmailInput.value = record.email || '';
                }
                if (assignDisplayName) {
                    assignDisplayName.textContent = record.name || '-';
                }
                if (assignDisplayEmail) {
                    assignDisplayEmail.textContent = record.email || '-';
                }

                userRoleCheckboxes.forEach(function (cb) {
                    cb.checked = roleSet.has(cb.value);
                });
            });
        });
    });
</script>
@endsection
