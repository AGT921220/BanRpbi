<?php

namespace App\Http\Controllers\Api;

use App\Features\Clients\Application\ClientHeader;
use App\Features\Clients\Application\SearchClientHeaders;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchClientHeadersRequest;
use Illuminate\Http\JsonResponse;

final class ClientHeaderController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const DATATABLE_ORDERABLE_COLUMNS = [
        0 => 'name',
        1 => 'email',
        2 => 'phone',
        3 => 'company',
        4 => 'created_at',
    ];

    /**
     * @var list<string>
     */
    private const API_ORDERABLE_COLUMNS = [
        'name',
        'email',
        'phone',
        'company',
        'created_at',
    ];

    /**
     * @var list<string>
     */
    private const SEARCHABLE_COLUMNS = [
        'name',
        'parentarl_surname',
        'email',
        'phone',
        'company',
    ];

    public function __construct(
        private readonly SearchClientHeaders $searchClientHeaders,
    ) {}

    public function index(SearchClientHeadersRequest $request): JsonResponse
    {
        $this->authorize(PermissionTypes::CLIENTS_VIEW);

        $isDataTable = $this->isDataTableRequest($request);

        [$offset, $limit] = $this->resolvePagination(
            request: $request,
            isDataTable: $isDataTable,
        );

        $modifiers = [];

        $search = $this->resolveSearch($request);

        if ($search !== null) {
            $modifiers[] = QueryFilter::whereAnyLike(
                fields: self::SEARCHABLE_COLUMNS,
                value: $search,
            );
        }

        $order = $this->resolveOrder(
            request: $request,
            isDataTable: $isDataTable,
        );

        if ($order !== null) {
            $modifiers[] = QueryOptions::orderBy(
                field: $order['field'],
                direction: $order['direction'],
            );
        }

        $modifiers[] = QueryOptions::offset($offset);
        $modifiers[] = QueryOptions::limit($limit);

        $result = ($this->searchClientHeaders)(
            modifiers: $modifiers,
            offset: $offset,
            limit: $limit,
        );

        $data = $result->data
            ->map(static fn (ClientHeader $header): array => $header->toArray())
            ->values();

        $response = [
            'data' => $data,
            'meta' => [
                'current_page' => $result->currentPage,
                'per_page' => $result->perPage,
                'total' => $result->total,
                'filtered' => $result->filtered,
                'last_page' => $result->lastPage,
            ],
        ];

        if ($isDataTable) {
            $response += [
                'draw' => $request->integer('draw'),
                'recordsTotal' => $result->total,
                'recordsFiltered' => $result->filtered,
            ];
        }

        return response()->json($response);
    }

    private function isDataTableRequest(SearchClientHeadersRequest $request): bool
    {
        return $request->has('draw')
            || $request->has('start')
            || $request->has('length');
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePagination(
        SearchClientHeadersRequest $request,
        bool $isDataTable,
    ): array {
        if ($isDataTable) {
            $offset = max($request->integer('start'), 0);
            $limit = $request->integer('length');

            if ($limit <= 0) {
                $limit = 20;
            }

            return [
                $offset,
                min($limit, 100),
            ];
        }

        $perPage = min(
            max($request->integer('per_page', 20), 1),
            100,
        );

        $page = max($request->integer('page', 1), 1);

        return [
            ($page - 1) * $perPage,
            $perPage,
        ];
    }

    private function resolveSearch(SearchClientHeadersRequest $request): ?string
    {
        $search = $request->input(
            'search.value',
            $request->input('search'),
        );

        if (! is_string($search)) {
            return null;
        }

        $search = trim($search);

        return $search !== '' ? $search : null;
    }

    /**
     * @return array{field: string, direction: string}|null
     */
    private function resolveOrder(
        SearchClientHeadersRequest $request,
        bool $isDataTable,
    ): ?array {
        if ($isDataTable) {
            $columnIndex = $request->input('order.0.column');

            if (
                $columnIndex === null
                || ! array_key_exists((int) $columnIndex, self::DATATABLE_ORDERABLE_COLUMNS)
            ) {
                return null;
            }

            return [
                'field' => self::DATATABLE_ORDERABLE_COLUMNS[(int) $columnIndex],
                'direction' => (string) $request->input('order.0.dir', 'asc'),
            ];
        }

        $field = $request->input('order_by');

        if (
            ! is_string($field)
            || ! in_array($field, self::API_ORDERABLE_COLUMNS, true)
        ) {
            return null;
        }

        return [
            'field' => $field,
            'direction' => (string) $request->input('order_direction', 'asc'),
        ];
    }
}
