@csrf

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $customer->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: CST001"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $customer->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Kios Tani Subur"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="customer_type" class="form-label">Tipe Pelanggan <span class="text-danger">*</span></label>
        <select
            class="form-select @error('customer_type') is-invalid @enderror"
            id="customer_type"
            name="customer_type"
            required
        >
            @foreach($types as $value => $label)
                <option value="{{ $value }}" @selected(old('customer_type', $customer->customer_type ?? 'perorangan') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('customer_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="contact_person" class="form-label">Kontak Person</label>
        <input
            type="text"
            class="form-control @error('contact_person') is-invalid @enderror"
            id="contact_person"
            name="contact_person"
            value="{{ old('contact_person', $customer->contact_person ?? '') }}"
            maxlength="255"
            placeholder="Nama PIC pelanggan"
        >
        @error('contact_person')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">No. Telepon</label>
        <input
            type="text"
            class="form-control @error('phone') is-invalid @enderror"
            id="phone"
            name="phone"
            value="{{ old('phone', $customer->phone ?? '') }}"
            maxlength="50"
            placeholder="Contoh: 08123456789"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            id="email"
            name="email"
            value="{{ old('email', $customer->email ?? '') }}"
            maxlength="255"
            placeholder="pelanggan@example.com"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="address" class="form-label">Alamat</label>
        <textarea
            class="form-control @error('address') is-invalid @enderror"
            id="address"
            name="address"
            rows="3"
            placeholder="Alamat lengkap pelanggan"
        >{{ old('address', $customer->address ?? '') }}</textarea>
        @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">Catatan</label>
        <textarea
            class="form-control @error('notes') is-invalid @enderror"
            id="notes"
            name="notes"
            rows="3"
            placeholder="Catatan relasi atau informasi tambahan"
        >{{ old('notes', $customer->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="is_active"
                name="is_active"
                value="1"
                {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ route('master.customers.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
