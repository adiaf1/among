<?php

namespace App\Http\Controllers;

use App\Models\SeedProduction;
use App\Models\SeedProductionStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeedProductionStepController extends Controller
{
    private const STATUSES = [
        'terjadwal' => 'Terjadwal',
        'proses' => 'Proses',
        'selesai' => 'Selesai',
    ];

    private const COST_TYPES = [
        'per_kg' => 'Per Kg',
        'langsung' => 'Langsung',
    ];

    private const POSITIONS = [
        'end' => 'Akhir',
        'before' => 'Sebelum',
        'after' => 'Sesudah',
    ];

    public function store(Request $request, SeedProduction $seedProduction): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:150'],
            'planned_date' => ['nullable', 'date'],
            'position' => ['required', 'string', Rule::in(array_keys(self::POSITIONS))],
            'reference_step_id' => ['nullable', 'uuid', 'exists:seed_production_steps,id'],
            'cost_type' => ['required', 'string', Rule::in(array_keys(self::COST_TYPES))],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['position'] !== 'end' && blank($validated['reference_step_id'] ?? null)) {
            throw ValidationException::withMessages([
                'reference_step_id' => 'Tahap acuan wajib dipilih.',
            ]);
        }

        DB::transaction(function () use ($seedProduction, $validated) {
            $seedProduction->load('steps');
            $referenceStep = null;

            if (filled($validated['reference_step_id'] ?? null)) {
                $referenceStep = $seedProduction->steps->firstWhere('id', $validated['reference_step_id']);

                if (! $referenceStep) {
                    throw ValidationException::withMessages([
                        'reference_step_id' => 'Tahap acuan tidak sesuai dengan produksi ini.',
                    ]);
                }

                if ($validated['position'] === 'after' && $referenceStep->stage === 'siap_salur') {
                    throw ValidationException::withMessages([
                        'reference_step_id' => 'Tahap baru tidak boleh ditempatkan setelah Siap Salur.',
                    ]);
                }
            }

            $insertOrder = $this->insertOrder($seedProduction, $validated['position'], $referenceStep);

            $seedProduction->steps()
                ->where('sort_order', '>=', $insertOrder)
                ->increment('sort_order');

            $seedProduction->steps()->create([
                'sort_order' => $insertOrder,
                'stage' => $this->generateStage($seedProduction, $validated['label']),
                'label' => $validated['label'],
                'planned_date' => $validated['planned_date'] ?? $referenceStep?->planned_date?->toDateString(),
                'cost_type' => $validated['cost_type'],
                'status' => 'terjadwal',
                'notes' => $validated['notes'] ?? null,
                'updated_by' => request()->user()?->id,
            ]);
        });

        return redirect()
            ->route('seed-productions.show', $seedProduction)
            ->with('success', 'Tahap produksi berhasil ditambahkan.');
    }

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
            'label' => ['required', 'string', 'max:150'],
            'planned_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'cost_per_kg' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'cost_type' => ['required', 'string', Rule::in(array_keys(self::COST_TYPES))],
            'cost' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'status' => ['required', 'string', Rule::in(array_keys(self::STATUSES))],
            'notes' => ['nullable', 'string'],
        ]);

        $costType = $validated['cost_type'];
        $actualDate = $validated['actual_date'] ?? ($validated['planned_date'] ?? null);
        $quantity = isset($validated['quantity']) ? (float) $validated['quantity'] : null;
        $costPerKg = isset($validated['cost_per_kg']) ? (float) $validated['cost_per_kg'] : null;
        $cost = (float) ($validated['cost'] ?? 0);

        if ($costType === 'per_kg') {
            $cost = $quantity !== null && $costPerKg !== null
                ? $quantity * $costPerKg
                : 0;
        } else {
            $costPerKg = null;
        }

        $step->update([
            'label' => $validated['label'],
            'planned_date' => $validated['planned_date'] ?? null,
            'actual_date' => $actualDate,
            'quantity' => $quantity,
            'cost_per_kg' => $costPerKg,
            'cost_type' => $costType,
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

    public static function costTypes(): array
    {
        return self::COST_TYPES;
    }

    public static function positions(): array
    {
        return self::POSITIONS;
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

    private function insertOrder(SeedProduction $seedProduction, string $position, ?SeedProductionStep $referenceStep): int
    {
        if ($position === 'before' && $referenceStep) {
            return (int) $referenceStep->sort_order;
        }

        if ($position === 'after' && $referenceStep) {
            return (int) $referenceStep->sort_order + 1;
        }

        $readyStep = $seedProduction->steps->firstWhere('stage', 'siap_salur');

        if ($readyStep) {
            return (int) $readyStep->sort_order;
        }

        return (int) $seedProduction->steps->max('sort_order') + 1;
    }

    private function generateStage(SeedProduction $seedProduction, string $label): string
    {
        $base = Str::slug($label, '_') ?: 'tahap';
        $stage = $base;
        $counter = 2;

        while ($seedProduction->steps()->where('stage', $stage)->exists()) {
            $stage = $base.'_'.$counter;
            $counter++;
        }

        return $stage;
    }
}
