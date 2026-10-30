<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Manifests\Application\FindManifest;
use App\Http\Controllers\Controller;

class ManifestController extends Controller
{
    public function index()
    {
        return view('dashboard.manifests.index');
    }

    public function show(int $manifestId, FindManifest $findManifest)
    {
        return response()->json(
            ['data' => $findManifest($manifestId)],
            200
        );
    }
}
