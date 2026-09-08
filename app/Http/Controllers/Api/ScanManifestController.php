<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ScanManifestController extends Controller
{
    public function show(int $manifestId, Request $request)
    {
        info($request->all());
        info('Manifest ID: ' . $manifestId);
        return response()->json([
            'message' => 'Servicios ordenados correctamente.',
        ], 201);
    }
    public function store(Request $request)
    {
        info($request->all());
        $manifestId = $request->input('manifest_id');
        info('Manifest ID: ' . $manifestId);

        return response()->json([
            'message' => 'Servicios ordenados correctamente.',
        ], 201);
    }
}
