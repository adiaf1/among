<?php

namespace App\Http\Controllers;

use App\Models\SeedProduction;
use App\Models\SeedProductionStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SeedProductionStepController extends Controller
{
    private const STATUSES = [
        'terjadwal' => 'Terjadwal',
        'proses' => 'Proses',
        'selesai' => 'Selesai',
    ];

    public function update(
        Request $request,
        SeedProduction $seedProduction,
        SeedProductionStep $step
    ): RedirectResponse {
        abort_unless($step->seed_production_id === $seedProduction->id, 404);

        $request->merge([
            'cost_per_kg' => $request->filled('cost_per_kg')
                ? preg_replace('/[^\d]/', '', (string) $request->input('cost_per_kg'))
                : null,
            'cost' => $request->filled('cost')
                ? preg_replace('/[^\d]/', '', (string) $request->input('cost'))
                : 0,
        ]);

        $validated = $request->validate([
            'planned_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'cost_per_kg' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string'],
        ]);

        $quantity = isset($validated['quantity']) ? (float) $validated['quantity'] : null;
        $costPerKg = isset($validated['cost_per_kg']) ? (float) $validated['cost_per_kg'] : null;
        $cost = $quantity !== null && $costPerKg !== null
            ? $quantity * $costPerKg
            : (float) ($validated['cost'] ?? 0);

        $step->update([
            'planned_date' => $validated['planned_date'] ?? null,
            'actual_date' => $validated['actual_date'] ?? null,
            'quantity' => $quantity,
            'cost_per_kg' => $costPerKg,
            'cost' => $cost,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $request->user()?->id,
        ]);

        self::syncProductionStatus($seedProduction->fresh('steps'));

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Tahap produksi berhasil diperbarui.');
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public static function syncProductionStatus(SeedProduction $seedProduction): void
    {
        if ($seedProduction->status === 'batal') {
            return;
        }

        $seedProduction->loadMissing('steps');
        $siapSalurDone = $seedProduction->steps
            ->firstWhere('stage', 'siap_salur')?->status === 'selesai';
        $status = $siapSalurDone ? 'siap_salur' : 'proses';

        if ($seedProduction->status !== $status) {
            $seedProduction->forceFill(['status' => $status])->save();
        }
    }
}
