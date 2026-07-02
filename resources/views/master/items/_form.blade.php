@csrf

@if(request('return_to') === 'purchases.create' || old('return_to') === 'purchases.create')
    <input type="hidden" name="return_to" value="purchases.create">
@endif

<div class="row g-4">
    <div class="col-md-4">
        <label for="code" class="form-label">Kode <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('code') is-invalid @enderror"
            id="code"
            name="code"
            value="{{ old('code', $item->code ?? '') }}"
            required
            maxlength="50"
            placeholder="Contoh: BRG001"
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-8">
        <label for="name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            id="name"
            name="name"
            value="{{ old('name', $item->name ?? '') }}"
            required
            maxlength="255"
            placeholder="Contoh: Benih Padi Ciherang ES"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
        <select
            class="form-select @error('category') is-invalid @enderror"
            id="category"
            name="category"
            required
        >
            @foreach($categories as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $item->category ?? 'lainnya') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="material_state" class="form-label">Kondisi / Tahap Material <span class="text-danger">*</span></label>
        <select
            class="form-select @error('material_state') is-invalid @enderror"
            id="material_state"
            name="material_state"
            required
        >
            @foreach($materialStates as $value => $label)
                <option value="{{ $value }}" @selected(old('material_state', $item->material_state ?? 'none') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Gunakan Basah/Kering untuk gabah agar siap dipakai di proses produksi.</div>
        @error('material_state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
        <input
            type="search"
            class="form-control @error('unit') is-invalid @enderror"
            id="unit"
            name="unit"
            value="{{ old('unit', $item->unit ?? 'kg') }}"
            list="unit-options"
            required
            maxlength="50"
            placeholder="Contoh: kg, pcs, roll"
            autocomplete="off"
        >
        <datalist id="unit-options">
            @foreach($unitOptions as $unitOption)
                <option value="{{ $unitOption }}"></option>
            @endforeach
        </datalist>
        <div class="form-text">Bisa ketik satuan baru atau pilih dari satuan yang pernah dipakai.</div>
        @error('unit')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="minimum_stock" class="form-label">Stok Minimum</label>
        <input
            type="number"
            class="form-control @error('minimum_stock') is-invalid @enderror"
            id="minimum_stock"
            name="minimum_stock"
            value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}"
            min="0"
            max="9999999999.99"
            step="0.01"
            placeholder="Contoh: 500"
        >
        @error('minimum_stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="rice_variety_id" class="form-label">Varietas Padi</label>
        <select
            class="form-select @error('rice_variety_id') is-invalid @enderror"
            id="rice_variety_id"
            name="rice_variety_id"
        >
            <option value="">Tidak terkait varietas</option>
            @foreach($riceVarieties as $riceVariety)
                <option value="{{ $riceVariety->id }}" @selected(old('rice_variety_id', $item->rice_variety_id ?? '') === $riceVariety->id)>
                    {{ $riceVariety->code }} - {{ $riceVariety->name }}
                </option>
            @endforeach
        </select>
        @error('rice_variety_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="seed_class_id" class="form-label">Kelas Benih</label>
        <select
            class="form-select @error('seed_class_id') is-invalid @enderror"
            id="seed_class_id"
            name="seed_class_id"
        >
            <option value="">Tidak terkait kelas benih</option>
            @foreach($seedClasses as $seedClass)
                <option value="{{ $seedClass->id }}" @selected(old('seed_class_id', $item->seed_class_id ?? '') === $seedClass->id)>
                    {{ $seedClass->code }} - {{ $seedClass->name }}
                </option>
            @endforeach
        </select>
        @error('seed_class_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="3"
            placeholder="Keterangan barang atau spesifikasi tambahan"
        >{{ old('description', $item->description ?? '') }}</textarea>
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
                {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="is_active">Aktif</label>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-5">
    <a href="{{ (request('return_to') === 'purchases.create' || old('return_to') === 'purchases.create') ? route('purchases.create') : route('master.items.index') }}" class="btn btn-label-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>
