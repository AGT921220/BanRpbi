<?php

namespace App\Http\Controllers\Api;

use App\Features\Services\Application\RegisterWarehouseEntry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ScanManifestController extends Controller
{
    public function __construct(
        private readonly RegisterWarehouseEntry $registerWarehouseEntry,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'manifest_id' => ['required', 'integer'],
        ]);

        $service = ($this->registerWarehouseEntry)(
            (int) $data['manifest_id'],
            (int) $request->user()->id,
        );

        return response()->json([
            'message' => 'Entrada de almacén registrada.',
            'data' => $service,
        ]);
    }
}
