<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ManifestController extends Controller
{
    public function sho(int $manifestId, Request $request)
    {
        return response()->json([
            'message' => 'Servicios ordenados correctamente.',
        ], 201);
    }
}
