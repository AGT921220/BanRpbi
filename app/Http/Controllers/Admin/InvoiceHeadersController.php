<?php

namespace App\Http\Controllers\Admin;

use App\Features\Invoices\Application\SearchInvoiceHeaders;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceHeadersController extends Controller
{
    public function __construct(
        private readonly SearchInvoiceHeaders $searchInvoiceHeaders,
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
            ($this->searchInvoiceHeaders)(
                filters: [
                    QueryOptions::offset($offset),
                    QueryOptions::limit($limit),
                    QueryOptions::orderBy(
                        $request->input('order_by') ?: 'invoices.id',
                        $request->input('order_direction') ?: 'desc',
                    ),
                ],
                draw: $draw,
            ),
        );
    }
}
