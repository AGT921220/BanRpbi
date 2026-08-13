<?php

namespace App\Http\Controllers\Admin;

use App\Features\Drivers\Application\SearchDriverHeaders;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverHeadersController extends Controller
{
    public function __construct(
        private readonly SearchDriverHeaders $searchDriverHeaders,
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
            ($this->searchDriverHeaders)(
                filters: [
                    QueryOptions::offset($offset),
                    QueryOptions::limit($limit),
                    $request->input('order_by') ? QueryOptions::orderBy(
                        $request->input('order_by'),
                        $request->input('order_direction'),
                    ) : 'asc',
                ],
                draw: $draw,
            ),
        );
    }
}
