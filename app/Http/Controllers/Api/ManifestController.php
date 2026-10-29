<?php

namespace App\Http\Controllers\Api;

use App\Features\Manifests\Application\FindManifestHeader;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ManifestController extends Controller
{
    public function __construct(
        private readonly FindManifestHeader $findManifestHeader,
    ) {}

    public function show(int $manifest): JsonResponse
    {
        $header = ($this->findManifestHeader)($manifest);

        if ($header === null) {
            return response()->json([
                'message' => 'No se encontró el manifiesto.',
            ], 404);
        }

        return response()->json([
            'data' => $header,
        ]);
    }
}
