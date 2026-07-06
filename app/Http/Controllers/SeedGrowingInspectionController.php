<?php

namespace App\Http\Controllers;

use App\Models\SeedGrowing;
use App\Models\SeedGrowingInspection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeedGrowingInspectionController extends Controller
{
    public function update(
        Request $request,
        SeedGrowing $seedGrowing,
        SeedGrowingInspection $inspection
    ): RedirectResponse {
        abort_unless($inspection->seed_growing_id === $seedGrowing->id, 404);

        if ($seedGrowing->status === 'batal') {
            return redirect()
                ->route('seed-growings.show', $seedGrowing)
                ->withErrors(['status' => 'Pemeriksaan lapang tidak bisa diperbarui karena penangkaran sudah batal.']);
        }

        $request->merge([
            'cost' => $request->filled('cost')
                ? preg_replace('/[^\d]/', '', (string) $request->input('cost'))
                : 0,
        ]);

        $validated = $request->validate([
            'planned_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['required', 'string', Rule::in(array_keys(SeedGrowingController::inspectionStatuses()))],
            'notes' => ['nullable', 'string'],
        ]);

        if (blank($validated['actual_date'] ?? null) && filled($validated['planned_date'] ?? null)) {
            $validated['actual_date'] = $validated['planned_date'];
        }

        $inspection->update([
            ...$validated,
            'cost' => $validated['cost'] ?? 0,
            'updated_by' => $request->user()?->id,
        ]);

        SeedGrowingController::syncStatusFromProcess($seedGrowing->fresh(['inspections', 'harvest']));

        return redirect()
            ->route('seed-growings.show', $seedGrowing)
            ->with('success', 'Data pemeriksaan lapang berhasil diperbarui.');
    }
}
