<?php

namespace App\Http\Requests\Admin;

use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;
use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'offset' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1'],
            'draw' => ['nullable', 'integer', 'min:1'],
            'order_by' => ['nullable', 'string'],
            'order_direction' => ['nullable', 'in:asc,desc'],
        ];
    }

    public function offset(): int
    {
        return max($this->integer('offset', 0), 0);
    }

    public function limit(): int
    {
        return max($this->integer('limit', 20), 1);
    }

    public function draw(): int
    {
        return max($this->integer('draw', 1), 1);
    }

    public function queryOptions(array $allowedFilters = []): array
    {
        $options = [
            QueryOptions::offset($this->offset()),
            QueryOptions::limit($this->limit()),
        ];

        if ($this->filled('order_by')) {
            $options[] = QueryOptions::orderBy(
                $this->input('order_by'),
                $this->input('order_direction', 'asc'),
            );
        }

        foreach ($this->input('filters', []) as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? null;
            $value = $filter['value'] ?? null;

            if (! $field || ! $operator) {
                continue;
            }

            if (! in_array($field, $allowedFilters, true)) {
                continue;
            }

            $option = $this->makeFilterOption(
                field: $field,
                operator: $operator,
                value: $value,
                comparison: $filter['comparison'] ?? '=',
            );

            if ($option !== null) {
                $options[] = $option;
            }
        }

        return $options;
    }
    private function makeFilterOption(
        string $field,
        string $operator,
        mixed $value,
        string $comparison = '=',
    ): ?QueryFilter {
        // dd($field, $operator, $value, $comparison);
        return match ($operator) {
            'where' => QueryFilter::where(
                field: $field,
                value: $value,
                comparison: $comparison,
            ),

            'where_in' => QueryFilter::whereIn(
                field: $field,
                values: (array) $value,
            ),

            'where_not_in' => QueryFilter::whereNotIn(
                field: $field,
                values: (array) $value,
            ),

            'where_between' => QueryFilter::whereBetween(
                field: $field,
                from: $value[0] ?? null,
                to: $value[1] ?? null,
            ),

            'where_null' => QueryFilter::whereNull(
                field: $field,
            ),

            'where_not_null' => QueryFilter::whereNotNull(
                field: $field,
            ),

            default => null,
        };
    }
}
