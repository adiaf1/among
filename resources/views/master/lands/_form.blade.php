@csrf

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $land->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: LHN001"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Lahan <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $land->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Sawah Blok Timur"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="farmer_id" class="form-label">Petani <span class="text-danger">*</span></label>
        <select class="form-select @error('farmer_id') is-invalid @enderror" id="farmer_id" name="farmer_id" required>
            <option value="">Pilih petani</option>
            @foreach($farmers as $farmer)
                <option value="{{ $farmer->id }}" {{ old('farmer_id', $land->farmer_id ?? '') === $farmer->id ? 'selected' : '' }}>
                    {{ $farmer->code }} - {{ $farmer->name }}
                </option>
            @endforeach
        </select>
        @error('farmer_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="area_size" class="form-label">Luas Lahan</label>
        <div class="input-group">
            <input
                type="number"
                step="0.01"
                min="0"
                class="form-control @error('area_size') is-invalid @enderror"
                id="area_size"
                name="area_size"
                value="{{ old('area_size', $land->area_size ?? '') }}"
                placeholder="Contoh: 2.50"
            >
            <span class="input-group-text">ha</span>
            @error('area_size')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-12">
        <label for="location" class="form-label">Alamat / Lokasi Lahan</label>
        <textarea
            class="form-control @error('location') is-invalid @enderror"
            id="location"
            name="location"
            rows="3"
            placeholder="Alamat, blok, desa, patok lokasi, atau deskripsi akses lahan"
        >{{ old('location', $land->location ?? '') }}</textarea>
        @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="latitude" class="form-label">Latitude</label>
        <input
            type="number"
            step="0.0000001"
            min="-90"
            max="90"
            class="form-control @error('latitude') is-invalid @enderror"
            id="latitude"
            name="latitude"
            value="{{ old('latitude', $land->latitude ?? '') }}"
            placeholder="Contoh: -6.5601234"
        >
        @error('latitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="longitude" class="form-label">Longitude</label>
        <input
            type="number"
            step="0.0000001"
            min="-180"
            max="180"
            class="form-control @error('longitude') is-invalid @enderror"
            id="longitude"
            name="longitude"
            value="{{ old('longitude', $land->longitude ?? '') }}"
            placeholder="Contoh: 107.7601234"
        >
        @error('longitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="certification_status" class="form-label">Status Kelayakan Sertifikasi <span class="text-danger">*</span></label>
        <select
            class="form-select @error('certification_status') is-invalid @enderror"
            id="certification_status"
            name="certification_status"
            required
        >
            @foreach($certificationStatuses as $value => $label)
                <option value="{{ $value }}" @selected(old('certification_status', $land->certification_status ?? 'belum_ditinjau') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Dipakai sebagai indikasi awal kelayakan lahan untuk penangkaran/sertifikasi benih.</div>
        @error('certification_status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="soil_type" class="form-label">Jenis Tanah</label>
        <input
            type="text"
            class="form-control @error('soil_type') is-invalid @enderror"
            id="soil_type"
            name="soil_type"
            value="{{ old('soil_type', $land->soil_type ?? '') }}"
            maxlength="100"
            placeholder="Contoh: Aluvial"
        >
        @error('soil_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="irrigation_type" class="form-label">Jenis Irigasi</label>
        <input
            type="text"
            class="form-control @error('irrigation_type') is-invalid @enderror"
            id="irrigation_type"
            name="irrigation_type"
            value="{{ old('irrigation_type', $land->irrigation_type ?? '') }}"
            maxlength="100"
            placeholder="Contoh: Teknis"
        >
        @error('irrigation_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="ownership_status" class="form-label">Status Kepemilikan</label>
        <input
            type="text"
            class="form-control @error('ownership_status') is-invalid @enderror"
            id="ownership_status"
            name="ownership_status"
            value="{{ old('ownership_status', $land->ownership_status ?? '') }}"
            maxlength="100"
            placeholder="Contoh: Milik sendiri"
        >
        @error('ownership_status')
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
            placeholder="Catatan kondisi lahan atau riwayat tanam"
        >{{ old('notes', $land->notes ?? '') }}</textarea>
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
                {{ old('is_active', $land->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ route('master.lands.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
