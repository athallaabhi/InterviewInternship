<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmissionType;

class InputFieldController extends Controller
{
    public function index(EmissionType $emissionType)
    {
        return response()->json($emissionType->inputFields);
    }
}
