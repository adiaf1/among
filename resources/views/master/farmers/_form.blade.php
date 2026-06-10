@csrf

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $farmer->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: PTR001"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Petani <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $farmer->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Budi Santoso"
        >
        @error('name')
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
            value="{{ old('phone', $farmer->phone ?? '') }}"
            maxlength="50"
            placeholder="Contoh: 08123456789"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="identity_number" class="form-label">NIK / Identitas</label>
        <input
            type="text"
            class="form-control @error('identity_number') is-invalid @enderror"
            id="identity_number"
            name="identity_number"
            value="{{ old('identity_number', $farmer->identity_number ?? '') }}"
            maxlength="100"
            placeholder="Nomor identitas petani"
        >
        @error('identity_number')
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
            placeholder="Alamat lengkap atau lokasi domisili"
        >{{ old('address', $farmer->address ?? '') }}</textarea>
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
            placeholder="Catatan kemitraan atau informasi tambahan"
        >{{ old('notes', $farmer->notes ?? '') }}</textarea>
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
                {{ old('is_active', $farmer->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ route('master.farmers.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
