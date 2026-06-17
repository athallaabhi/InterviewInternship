<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmissionType;

class EmissionTypeController extends Controller
{
    public function index()
    {
        return response()->json(
            EmissionType::select('id', 'name', 'slug', 'description', 'formula_display', 'unit')->get()
        );
    }

    public function schema(EmissionType $emissionType)
    {
        $emissionType->load([
            'categories' => fn($q) => $q->orderBy('sort_order'),
            'categories.values',
            'inputFields',
        ]);

        return response()->json([
            'id'              => $emissionType->id,
            'name'            => $emissionType->name,
            'slug'            => $emissionType->slug,
            'formula_display' => $emissionType->formula_display,
            'unit'            => $emissionType->unit,
            'categories'      => $emissionType->categories->map(fn($cat) => [
                'id'           => $cat->id,
                'name'         => $cat->name,
                'display_name' => $cat->display_name,
                'sort_order'   => $cat->sort_order,
                'values'       => $cat->values->map(fn($v) => [
                    'id'    => $v->id,
                    'code'  => $v->code,
                    'label' => $v->label,
                ]),
            ]),
            'input_fields'    => $emissionType->inputFields->map(fn($f) => [
                'id'           => $f->id,
                'name'         => $f->name,
                'display_name' => $f->display_name,
                'unit'         => $f->unit,
            ]),
        ]);
    }
}
