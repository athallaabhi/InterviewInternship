<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CalculatorController extends Controller
{
    public function index()
    {
        return view('calculator.index');
    }
}
