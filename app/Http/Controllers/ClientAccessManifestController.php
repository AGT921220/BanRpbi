<?php

namespace App\Http\Controllers;

use App\Features\Manifests\Application\FindManifest;
use App\Models\Manifest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientAccessManifestController extends Controller
{
    public function __construct(private FindManifest $findManifest) {}

    public function index(Request $request)
    {
        info($request->all());
    }

    public function show(Request $request, string $manifestPublicUuid): View|JsonResponse
    {
        $manifest = Manifest::query()
            ->where('public_uuid', $manifestPublicUuid)
            ->first();

        if ($manifest === null) {
            abort(404);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'data' => ($this->findManifest)($manifest->id),
            ]);
        }

        return view('client-access.manifests.show', [
            'manifestPublicUuid' => $manifest->public_uuid,
        ]);
    }
}
