@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Manajemen Pengguna</h4>
            <p class="text-muted mb-0">Kelola akun pengguna dan kaitkan ke satu atau beberapa grup permission.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('permission-groups.index') }}" class="btn btn-outline-primary">
                <i class="icon-base bx bx-check-shield me-1"></i> Kelola Grup Permission
            </a>
            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="offcanvas"
                data-bs-target="#userOffcanvas"
                data-mode="create"
            >
                <i class="icon-base bx bx-plus me-1"></i> Tambah Pengguna
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

    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-center">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="col-md-4 col-lg-3">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Grup Permission</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->name }}" @selected($roleFilter === $roleOption->name)>
                                {{ $roleOption->name }}
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
                        <a href="{{ route('users.index') }}" class="btn btn-label-secondary">Reset</a>
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
                        <th>Grup Permission</th>
                        <th>Hak Akses Modul</th>
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
                                @forelse($user->roles as $roleBadgeItem)
                                    @php
                                        $badgeColor = in_array($roleBadgeItem->name, ['super-admin', 'admin'], true)
                                            ? 'bg-label-danger'
                                            : ($roleBadgeItem->name === 'editor' ? 'bg-label-primary' : 'bg-label-info');
                                    @endphp
                                    <span class="badge {{ $badgeColor }} me-1">{{ $roleBadgeItem->name }}</span>
                                @empty
                                    <span class="text-muted small">Belum dikaitkan</span>
                                @endforelse
                            </td>
                            <td>
                                @if($userIsSuper)
                                    <span class="badge bg-label-success">Akses Penuh (Super Admin)</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ count($userPermissions) }} / {{ $totalPermissionsCount }} Modul</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-icon btn-outline-primary"
                                        title="Edit Pengguna & Grup Permission"
                                        data-bs-toggle="offcanvas"
                                        data-bs-target="#userOffcanvas"
                                        data-mode="edit"
                                        data-record="{{ json_encode($userRecord) }}"
                                    >
                                        <span class="icon-base bx bx-edit-alt icon-sm"></span>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <form
                                            action="{{ route('users.destroy', $user) }}"
                                            method="POST"
                                            class="d-inline"
                                            data-confirm-delete
                                            data-confirm-title="Hapus pengguna?"
                                            data-confirm-text="Data {{ $user->name }} akan dihapus dari sistem."
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                                <span class="icon-base bx bx-trash icon-sm"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center gap-2">
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

    <!-- Offcanvas Tambah / Edit Pengguna -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="userOffcanvas" aria-labelledby="userOffcanvasTitle">
        <div class="offcanvas-header border-bottom">
            <h5 id="userOffcanvasTitle" class="offcanvas-title">Tambah Pengguna</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="userOffcanvasForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="_method" id="userOffcanvasMethod" value="POST">

                <div class="mb-4">
                    <label for="user_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="user_name" name="name" required>
                </div>

                <div class="mb-4">
                    <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="user_email" name="email" required>
                </div>

                <div class="mb-4">
                    <label for="user_password" class="form-label">
                        Password <span class="text-danger" id="userPasswordRequiredMark">*</span>
                    </label>
                    <input type="password" class="form-control" id="user_password" name="password" minlength="6">
                    <div class="form-text" id="userPasswordHint">Minimal 6 karakter.</div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Grup Permission <span class="text-danger">*</span></label>
                        <a href="{{ route('permission-groups.index') }}" class="small">+ Buat Grup Baru</a>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($roles as $roleItem)
                            @php
                                $isSuperItem = in_array($roleItem->name, ['super-admin', 'admin'], true);
                                $permCount = $isSuperItem ? $totalPermissionsCount : $roleItem->permissions_count;
                            @endphp
                            <label class="form-check p-3 border rounded d-flex align-items-start gap-2" for="offcanvas_role_{{ $roleItem->id }}">
                                <input
                                    class="form-check-input mt-1 offcanvas-role-checkbox"
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $roleItem->name }}"
                                    id="offcanvas_role_{{ $roleItem->id }}"
                                >
                                <span class="d-block">
                                    <span class="fw-medium d-block">{{ $roleItem->name }}</span>
                                    <small class="text-muted">
                                        {{ $isSuperItem ? 'Akses Penuh (Super Admin)' : "{$permCount} modul diizinkan" }}
                                    </small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="userOffcanvasSubmit">Simpan</button>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formEl = document.getElementById('userOffcanvasForm');
        const methodEl = document.getElementById('userOffcanvasMethod');
        const titleEl = document.getElementById('userOffcanvasTitle');
        const submitEl = document.getElementById('userOffcanvasSubmit');
        const nameInput = document.getElementById('user_name');
        const emailInput = document.getElementById('user_email');
        const passwordInput = document.getElementById('user_password');
        const passwordRequiredMark = document.getElementById('userPasswordRequiredMark');
        const passwordHint = document.getElementById('userPasswordHint');
        const roleCheckboxes = Array.from(document.querySelectorAll('.offcanvas-role-checkbox'));
        const storeUrl = @json(route('users.store'));

        document.querySelectorAll('[data-bs-target="#userOffcanvas"]').forEach(function (button) {
            button.addEventListener('click', function () {
                const mode = button.getAttribute('data-mode') || 'create';

                if (mode === 'edit') {
                    const record = JSON.parse(button.getAttribute('data-record') || '{}');
                    const selectedRoles = new Set(record.roles || []);

                    titleEl.textContent = 'Edit Pengguna';
                    submitEl.textContent = 'Perbarui';
                    formEl.action = record.update_url || storeUrl;
                    methodEl.value = 'PUT';
                    nameInput.value = record.name || '';
                    emailInput.value = record.email || '';
                    passwordInput.value = '';
                    passwordInput.required = false;
                    passwordRequiredMark.classList.add('d-none');
                    passwordHint.textContent = 'Kosongkan jika tidak ingin mengubah password.';

                    roleCheckboxes.forEach(function (cb) {
                        cb.checked = selectedRoles.has(cb.value);
                    });
                } else {
                    titleEl.textContent = 'Tambah Pengguna';
                    submitEl.textContent = 'Simpan';
                    formEl.action = storeUrl;
                    methodEl.value = 'POST';
                    nameInput.value = '';
                    emailInput.value = '';
                    passwordInput.value = '';
                    passwordInput.required = true;
                    passwordRequiredMark.classList.remove('d-none');
                    passwordHint.textContent = 'Minimal 6 karakter.';

                    roleCheckboxes.forEach(function (cb) {
                        cb.checked = cb.value === 'editor';
                    });
                }
            });
        });
    });
</script>
@endsection
