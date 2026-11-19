<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanySettingController extends Controller
{
    public function edit(): View
    {
        CompanySetting::clearCachedInstance();

        return view('settings.company', [
            'setting' => CompanySetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'invoice_footer_note' => ['nullable', 'string'],
        ]);

        $setting = CompanySetting::query()->first() ?? new CompanySetting();
        $logoPath = $setting->logo_path;

        if ($request->boolean('remove_logo') && $logoPath) {
            $fullOldPath = public_path($logoPath);
            if (File::exists($fullOldPath)) {
                File::delete($fullOldPath);
            }
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                $fullOldPath = public_path($logoPath);
                if (File::exists($fullOldPath)) {
                    File::delete($fullOldPath);
                }
            }

            $uploadDir = public_path('uploads/company');
            if (! File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $file = $request->file('logo');
            $filename = 'logo-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $logoPath = 'uploads/company/'.$filename;
        }

        $setting->fill([
            'company_name' => trim($validated['company_name']),
            'tagline' => $validated['tagline'] ? trim($validated['tagline']) : null,
            'logo_path' => $logoPath,
            'address' => $validated['address'] ? trim($validated['address']) : null,
            'phone' => $validated['phone'] ? trim($validated['phone']) : null,
            'email' => $validated['email'] ? trim($validated['email']) : null,
            'website' => $validated['website'] ? trim($validated['website']) : null,
            'license_number' => $validated['license_number'] ? trim($validated['license_number']) : null,
            'city' => $validated['city'] ? trim($validated['city']) : null,
            'invoice_footer_note' => $validated['invoice_footer_note'] ? trim($validated['invoice_footer_note']) : null,
        ]);
        $setting->save();

        CompanySetting::clearCachedInstance();

        return redirect()
            ->route('settings.company.edit')
            ->with('success', 'Pengaturan identitas perusahaan & kop dokumen berhasil disimpan.');
    }
}
