<?php

namespace App\Services;

use App\Models\CoefficientValue;
use App\Models\EmissionType;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class EmissionCalculationService
{
    private ExpressionLanguage $expr;

    public function __construct()
    {
        $this->expr = new ExpressionLanguage();
    }

    /**
     * @param int   $emissionTypeId
     * @param array $inputs             [input_field_id => float value]
     * @param array $categorySelections [category_id => category_value_id]
     * @return array{total_emission: float, unit: string, formula_evaluated: string, coefficients_used: array}
     */
    public function calculate(int $emissionTypeId, array $inputs, array $categorySelections): array
    {
        $type = EmissionType::with([
            'inputFields',
            'coefficients.dependentCategories',
            'coefficients.values.categoryValues',
        ])->findOrFail($emissionTypeId);

        $vars = [];

        // Map input field values by slug name
        foreach ($type->inputFields as $field) {
            $value = $inputs[$field->id] ?? null;
            if ($value === null) {
                throw new \InvalidArgumentException("Missing input for field: {$field->display_name}");
            }
            $vars["input_{$field->name}"] = (float) $value;
        }

        $coefficientsUsed = [];

        foreach ($type->coefficients as $coefficient) {
            $depCategoryIds = $coefficient->dependentCategories->pluck('id')->toArray();

            if (empty($depCategoryIds)) {
                // Constant coefficient — no category dependency
                $cv = $coefficient->values->first();
                if (!$cv) {
                    throw new \RuntimeException("No value found for constant coefficient: {$coefficient->display_name}");
                }
                $vars["coef_{$coefficient->name}"] = $cv->value;
                $coefficientsUsed[] = [
                    'name'     => $coefficient->display_name,
                    'slug'     => $coefficient->name,
                    'value'    => $cv->value,
                    'based_on' => $cv->based_on ?? '(konstan)',
                ];
                continue;
            }

            // Get selected category_value_ids for this coefficient's dependencies
            $selectedCvIds = [];
            $basedOnLabels = [];
            foreach ($depCategoryIds as $catId) {
                $catValueId = $categorySelections[$catId] ?? null;
                if ($catValueId === null) {
                    throw new \InvalidArgumentException("Missing category selection for coefficient: {$coefficient->display_name}");
                }
                $selectedCvIds[] = (int) $catValueId;
            }

            // Exact-match lookup via pivot table
            $depCount = count($selectedCvIds);
            $matchedCvId = DB::table('coefficient_values as cv')
                ->where('cv.coefficient_id', $coefficient->id)
                ->whereRaw(
                    '(SELECT COUNT(*) FROM coefficient_value_category_pivot p WHERE p.coefficient_value_id = cv.id AND p.category_value_id IN (' . implode(',', $selectedCvIds) . ')) = ?',
                    [$depCount]
                )
                ->whereRaw(
                    '(SELECT COUNT(*) FROM coefficient_value_category_pivot p WHERE p.coefficient_value_id = cv.id) = ?',
                    [$depCount]
                )
                ->value('cv.id');

            if (!$matchedCvId) {
                throw new \RuntimeException("No coefficient value found for '{$coefficient->display_name}' with the selected category combination.");
            }

            $cv = CoefficientValue::with('categoryValues')->find($matchedCvId);
            $vars["coef_{$coefficient->name}"] = $cv->value;

            $coefficientsUsed[] = [
                'name'     => $coefficient->display_name,
                'slug'     => $coefficient->name,
                'value'    => $cv->value,
                'based_on' => $cv->based_on ?? $cv->categoryValues->pluck('label')->join(', '),
            ];
        }

        $total = (float) $this->expr->evaluate($type->formula, $vars);

        // Build human-readable evaluated expression
        $formulaEvaluated = $this->buildFormulaString($type->formula, $vars, $total, $type->unit);

        return [
            'total_emission'    => round($total, 4),
            'unit'              => $type->unit,
            'formula_evaluated' => $formulaEvaluated,
            'coefficients_used' => $coefficientsUsed,
        ];
    }

    private function buildFormulaString(string $formula, array $vars, float $result, string $unit): string
    {
        $parts = explode('*', $formula);
        $valueParts = array_map(function (string $part) use ($vars): string {
            $key = trim($part);
            return isset($vars[$key]) ? (string) $vars[$key] : $key;
        }, $parts);

        return implode(' × ', $valueParts) . ' = ' . number_format($result, 4) . ' ' . $unit;
    }
}
