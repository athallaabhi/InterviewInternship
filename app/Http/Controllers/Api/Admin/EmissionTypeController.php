<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmissionType;

class EmissionTypeController extends Controller
{
    public function index()
    {
        return response()->json(
            EmissionType::withCount(['categories', 'coefficients', 'inputFields'])->get()
        );
    }
}
