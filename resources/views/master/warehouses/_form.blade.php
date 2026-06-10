@csrf

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $warehouse->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: GDG001"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Gudang <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $warehouse->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Gudang Benih Utama"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="person_in_charge" class="form-label">Penanggung Jawab</label>
        <input
            type="text"
            class="form-control @error('person_in_charge') is-invalid @enderror"
            id="person_in_charge"
            name="person_in_charge"
            value="{{ old('person_in_charge', $warehouse->person_in_charge ?? '') }}"
            maxlength="255"
            placeholder="Nama penanggung jawab gudang"
        >
        @error('person_in_charge')
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
            value="{{ old('phone', $warehouse->phone ?? '') }}"
            maxlength="50"
            placeholder="Contoh: 08123456789"
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="capacity_kg" class="form-label">Kapasitas (Kg)</label>
        <input
            type="number"
            class="form-control @error('capacity_kg') is-invalid @enderror"
            id="capacity_kg"
            name="capacity_kg"
            value="{{ old('capacity_kg', $warehouse->capacity_kg ?? '') }}"
            min="0"
            max="9999999999.99"
            step="0.01"
            placeholder="Contoh: 25000"
        >
        @error('capacity_kg')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="address" class="form-label">Alamat / Lokasi</label>
        <textarea
            class="form-control @error('address') is-invalid @enderror"
            id="address"
            name="address"
            rows="3"
            placeholder="Alamat atau lokasi gudang"
        >{{ old('address', $warehouse->address ?? '') }}</textarea>
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
            placeholder="Catatan gudang atau aturan penyimpanan"
        >{{ old('notes', $warehouse->notes ?? '') }}</textarea>
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
                {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ route('master.warehouses.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
