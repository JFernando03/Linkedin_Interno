<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Industria;
use Illuminate\Http\Request;

class IndustriaController extends Controller
{
    public function index()
    {
        $industrias = Industria::orderBy('industria','asc')->get();

        return response()->json([
            'success' => true,
            'industrias' => $industrias,
        ], 200);
    }
}
