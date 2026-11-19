@php
    $company = \App\Models\CompanySetting::current();
    $companyName = $company->company_name ?: config('app.name');
    $logoUrl = $company->logoUrl();
    $contactParts = array_filter([
        $company->phone ? 'Telp/HP: '.$company->phone : null,
        $company->email ? 'Email: '.$company->email : null,
        $company->website ?: null,
    ]);
@endphp

<div class="{{ $wrapperClass ?? 'print-header' }}" style="align-items: center; border-bottom: 2px solid #111; display: flex; justify-content: space-between; gap: 16px; margin-bottom: 14px; padding-bottom: 10px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo {{ $companyName }}" style="max-height: 58px; max-width: 110px; object-fit: contain;">
        @endif
        <div>
            <h3 class="mb-0" style="font-size: 16px; font-weight: 700; margin: 0 0 2px 0; color: #111;">{{ $companyName }}</h3>
            @if($company->tagline)
                <div style="font-size: 11px; font-weight: 600; color: #333;">{{ $company->tagline }}</div>
            @endif
            @if($company->address)
                <div style="font-size: 10.5px; color: #333;">{{ $company->address }}</div>
            @endif
            @if(! empty($contactParts))
                <div style="font-size: 10.5px; color: #333;">{{ implode(' | ', $contactParts) }}</div>
            @endif
            @if($company->license_number)
                <div style="font-size: 10px; color: #444;">{{ $company->license_number }}</div>
            @endif
        </div>
    </div>

    <div class="text-end" style="text-align: right; white-space: nowrap;">
        <div class="print-title" style="font-size: 14px; font-weight: 700; letter-spacing: 0.3px; text-transform: uppercase;">
            {{ $documentTitle ?? 'Dokumen' }}
        </div>
        @if(! empty($documentSubtitle))
            <div style="font-size: 11.5px; font-weight: 600;">{{ $documentSubtitle }}</div>
        @endif
        @if(! empty($documentMeta))
            <div style="font-size: 10px; color: #444;">{{ $documentMeta }}</div>
        @endif
    </div>
</div>
