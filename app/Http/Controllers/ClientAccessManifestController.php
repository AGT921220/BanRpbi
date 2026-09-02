<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

final class ClientAccessManifestController extends Controller
{

    public function index(Request $request)
    {
        info($request->all());
    }
    public function show(Request $request, $manifestId)
    {
        info($request->all());
        info('Manifest ID: ' . $manifestId);
    }
}
