<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\EmissionType;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(EmissionType $emissionType)
    {
        return response()->json(
            $emissionType->categories()->withCount('values')->orderBy('sort_order')->get()
        );
    }

    public function indexValues(Category $category)
    {
        return response()->json($category->values);
    }

    public function storeValue(Request $request, Category $category)
    {
        $data = $request->validate([
            'code'  => 'required|string|max:255',
            'label' => 'required|string|max:255',
        ]);
        $data['category_id'] = $category->id;

        return response()->json(CategoryValue::create($data), 201);
    }

    public function updateValue(Request $request, CategoryValue $categoryValue)
    {
        $data = $request->validate([
            'code'  => 'sometimes|string|max:255',
            'label' => 'sometimes|string|max:255',
        ]);
        $categoryValue->update($data);
        return response()->json($categoryValue);
    }

    public function destroyValue(CategoryValue $categoryValue)
    {
        $categoryValue->delete();
        return response()->json(null, 204);
    }
}
