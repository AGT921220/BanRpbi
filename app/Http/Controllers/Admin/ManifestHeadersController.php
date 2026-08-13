<?php

namespace App\Http\Controllers\Admin;

use App\Features\Manifests\Application\SearchManifestHeaders;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManifestHeadersController extends Controller
{
    public function __construct(
        private readonly SearchManifestHeaders $searchManifestHeaders,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $offset = max($request->integer('offset', 0), 0);
        $limit = $request->integer('limit', 20);

        if ($limit <= 0) {
            $limit = 20;
        }

        $draw = max($request->integer('draw', 1), 1);

        return response()->json(
            ($this->searchManifestHeaders)(
                filters: [
                    $request->input('order_by')?QueryOptions::orderBy($request->input('order_by'),
                    $request->input('order_direction')):'asc',
                ],
                draw: $draw,
            ),
        );
    }
}
