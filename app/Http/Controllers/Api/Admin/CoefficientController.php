<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coefficient;
use App\Models\CoefficientValue;
use App\Models\EmissionType;
use Illuminate\Http\Request;

class CoefficientController extends Controller
{
    public function index(EmissionType $emissionType)
    {
        return response()->json(
            $emissionType->coefficients()->with('dependentCategories')->withCount('values')->get()
        );
    }

    public function store(Request $request, EmissionType $emissionType)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'display_name'   => 'required|string|max:255',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $coef = Coefficient::create([
            'emission_type_id' => $emissionType->id,
            'name'             => $data['name'],
            'display_name'     => $data['display_name'],
        ]);

        if (!empty($data['category_ids'])) {
            $coef->dependentCategories()->attach($data['category_ids']);
        }

        return response()->json($coef->load('dependentCategories'), 201);
    }

    public function update(Request $request, Coefficient $coefficient)
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'display_name'   => 'sometimes|string|max:255',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $coefficient->update([
            'name'         => $data['name'] ?? $coefficient->name,
            'display_name' => $data['display_name'] ?? $coefficient->display_name,
        ]);

        if (array_key_exists('category_ids', $data)) {
            $coefficient->dependentCategories()->sync($data['category_ids'] ?? []);
        }

        return response()->json($coefficient->load('dependentCategories'));
    }

    public function destroy(Coefficient $coefficient)
    {
        $coefficient->delete();
        return response()->json(null, 204);
    }

    // Coefficient Values
    public function indexValues(Coefficient $coefficient)
    {
        return response()->json(
            $coefficient->values()->with('categoryValues')->get()->map(fn($cv) => [
                'id'              => $cv->id,
                'value'           => $cv->value,
                'based_on'        => $cv->based_on,
                'category_values' => $cv->categoryValues->map(fn($catVal) => [
                    'id'          => $catVal->id,
                    'label'       => $catVal->label,
                    'category_id' => $catVal->category_id,
                ]),
            ])
        );
    }

    public function storeValue(Request $request, Coefficient $coefficient)
    {
        $data = $request->validate([
            'value'                => 'required|numeric',
            'based_on'             => 'nullable|string|max:255',
            'category_value_ids'   => 'nullable|array',
            'category_value_ids.*' => 'integer|exists:category_values,id',
        ]);

        $cv = CoefficientValue::create([
            'coefficient_id' => $coefficient->id,
            'value'          => $data['value'],
            'based_on'       => $data['based_on'] ?? null,
        ]);

        if (!empty($data['category_value_ids'])) {
            $cv->categoryValues()->attach($data['category_value_ids']);
        }

        return response()->json($cv->load('categoryValues'), 201);
    }

    public function updateValue(Request $request, CoefficientValue $coefficientValue)
    {
        $data = $request->validate([
            'value'                => 'sometimes|numeric',
            'based_on'             => 'nullable|string|max:255',
            'category_value_ids'   => 'nullable|array',
            'category_value_ids.*' => 'integer|exists:category_values,id',
        ]);

        $updateFields = [];
        if (isset($data['value']))    $updateFields['value']    = $data['value'];
        if (\array_key_exists('based_on', $data)) $updateFields['based_on'] = $data['based_on'];
        if ($updateFields) $coefficientValue->update($updateFields);

        if (array_key_exists('category_value_ids', $data)) {
            $coefficientValue->categoryValues()->sync($data['category_value_ids'] ?? []);
        }

        return response()->json($coefficientValue->load('categoryValues'));
    }

    public function destroyValue(CoefficientValue $coefficientValue)
    {
        $coefficientValue->delete();
        return response()->json(null, 204);
    }
}
