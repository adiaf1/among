@csrf

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $riceVariety->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: INP32"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Varietas <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $riceVariety->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Inpari 32"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="4"
            placeholder="Catatan karakter varietas, umur panen, atau keterangan lain"
        >{{ old('description', $riceVariety->description ?? '') }}</textarea>
        @error('description')
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
                {{ old('is_active', $riceVariety->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ route('master.rice-varieties.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
