@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4">
        <div>
            <h4 class="mb-1">Pengaturan Perusahaan & Kop Dokumen</h4>
            <p class="text-muted mb-0">Atur logo, nama perusahaan, alamat, dan kontak yang otomatis tampil pada kop Invoice, Surat Jalan, dan seluruh Laporan.</p>
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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Identitas Perusahaan</h5>
                </div>
                <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="logo" class="form-label">Logo Perusahaan</label>
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-2">
                                    @if($setting->logoUrl())
                                        <div class="border rounded p-2 bg-light-subtle">
                                            <img
                                                src="{{ $setting->logoUrl() }}"
                                                alt="Logo Perusahaan"
                                                id="logo-preview-thumb"
                                                style="max-height: 64px; max-width: 140px; object-fit: contain;"
                                            >
                                        </div>
                                    @else
                                        <div class="border rounded p-3 bg-light-subtle text-muted small" id="logo-empty-box">
                                            Belum ada logo
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <input
                                            type="file"
                                            id="logo"
                                            name="logo"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="form-control @error('logo') is-invalid @enderror"
                                        >
                                        <div class="form-text">Format PNG, JPG, atau WEBP (maks. 2 MB). Disarankan latar transparan.</div>
                                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                @if($setting->logoUrl())
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                        <label class="form-check-label text-danger small" for="remove_logo">Hapus logo saat ini</label>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Nama Perusahaan / Usaha <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    value="{{ old('company_name', $setting->company_name) }}"
                                    class="form-control @error('company_name') is-invalid @enderror"
                                    placeholder="Contoh: PT. Benih Unggul Nusantara"
                                    required
                                >
                                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tagline" class="form-label">Subjudul / Bidang Usaha</label>
                                <input
                                    type="text"
                                    id="tagline"
                                    name="tagline"
                                    value="{{ old('tagline', $setting->tagline) }}"
                                    class="form-control @error('tagline') is-invalid @enderror"
                                    placeholder="Contoh: Produsen & Penyalur Benih Padi Bersertifikat"
                                >
                                @error('tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Alamat Lengkap</label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows="2"
                                    class="form-control @error('address') is-invalid @enderror"
                                    placeholder="Contoh: Jl. Raya Pertanian No. 12, Kec. Trimurjo, Kab. Lampung Tengah"
                                >{{ old('address', $setting->address) }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">No Telepon / HP / WhatsApp</label>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone', $setting->phone) }}"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    placeholder="Contoh: 0812-3456-7890"
                                >
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Perusahaan</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $setting->email) }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Contoh: admin@benihunggul.co.id"
                                >
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="website" class="form-label">Website</label>
                                <input
                                    type="text"
                                    id="website"
                                    name="website"
                                    value="{{ old('website', $setting->website) }}"
                                    class="form-control @error('website') is-invalid @enderror"
                                    placeholder="Contoh: www.benihunggul.co.id"
                                >
                                @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="license_number" class="form-label">NPWP / No. Izin Produsen Benih</label>
                                <input
                                    type="text"
                                    id="license_number"
                                    name="license_number"
                                    value="{{ old('license_number', $setting->license_number) }}"
                                    class="form-control @error('license_number') is-invalid @enderror"
                                    placeholder="Contoh: No. Rekomendasi Produsen Benih: 521/012/BPSB"
                                >
                                @error('license_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="city" class="form-label">Kota (Untuk Tanggal & Tanda Tangan)</label>
                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    value="{{ old('city', $setting->city) }}"
                                    class="form-control @error('city') is-invalid @enderror"
                                    placeholder="Contoh: Lampung Tengah"
                                >
                                @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="invoice_footer_note" class="form-label">Catatan Kaki Invoice / Surat Jalan</label>
                                <textarea
                                    id="invoice_footer_note"
                                    name="invoice_footer_note"
                                    rows="2"
                                    class="form-control @error('invoice_footer_note') is-invalid @enderror"
                                    placeholder="Contoh: Terima kasih atas kepercayaan Anda. Pastikan barang diperiksa saat diterima."
                                >{{ old('invoice_footer_note', $setting->invoice_footer_note) }}</textarea>
                                @error('invoice_footer_note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-save me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Pratinjau Kop Dokumen (Invoice & Laporan)</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="border rounded p-4 bg-white text-dark">
                        @include('partials.print-letterhead', [
                            'wrapperClass' => 'preview-letterhead',
                            'documentTitle' => 'Invoice Penjualan',
                            'documentSubtitle' => 'SO-'.now()->format('Ymd').'-0001',
                            'documentMeta' => 'Tanggal: '.now()->format('d M Y'),
                        ])

                        <div class="small text-muted mt-3">
                            <div class="mb-2">Contoh area isi dokumen / tabel barang...</div>
                            <div class="border-top pt-2 mt-3" style="font-size: 11px;">
                                @if($setting->invoice_footer_note)
                                    <em>Catatan: {{ $setting->invoice_footer_note }}</em>
                                @else
                                    <em>Catatan kaki invoice akan tampil di sini jika diisi.</em>
                                @endif
                            </div>
                            <div class="text-end mt-3" style="font-size: 11px;">
                                {{ $setting->city ? $setting->city.', ' : '' }}{{ now()->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="form-text mt-3">
                        Kop di atas otomatis diterapkan pada cetak <strong>Invoice Pembelian</strong>, <strong>Invoice / Surat Jalan Penjualan</strong>, serta seluruh <strong>7 halaman Laporan</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
