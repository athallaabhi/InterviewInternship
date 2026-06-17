<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmissionCalculationService;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function __construct(private EmissionCalculationService $calculationService) {}

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'emission_type_id'                   => 'required|integer|exists:emission_types,id',
            'inputs'                             => 'required|array',
            'inputs.*.input_field_id'            => 'required|integer|exists:input_fields,id',
            'inputs.*.value'                     => 'required|numeric',
            'category_selections'                => 'required|array',
            'category_selections.*.category_id'      => 'required|integer|exists:categories,id',
            'category_selections.*.category_value_id' => 'required|integer|exists:category_values,id',
        ]);

        $inputs = collect($validated['inputs'])
            ->keyBy('input_field_id')
            ->map(fn($i) => $i['value'])
            ->toArray();

        $categorySelections = collect($validated['category_selections'])
            ->keyBy('category_id')
            ->map(fn($s) => $s['category_value_id'])
            ->toArray();

        try {
            $result = $this->calculationService->calculate(
                $validated['emission_type_id'],
                $inputs,
                $categorySelections
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
