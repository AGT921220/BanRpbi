<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

final class BuilderFilter
{
    /**
     * @param array<int, mixed> $modifiers
     */
    public function __invoke(
        Builder $builder,
        array $modifiers = [],
    ): Builder {
        $hasLimit = false;
        $hasOffset = false;
        $hasOrderBy = false;

        foreach ($modifiers as $modifier) {
            if (! $modifier instanceof QueryModifierInterface) {
                continue;
            }

            if ($modifier instanceof QueryOptions) {
                match ($modifier->getType()) {
                    'limit' => $hasLimit = true,
                    'offset' => $hasOffset = true,
                    'order_by' => $hasOrderBy = true,
                    default => null,
                };
            }

            $builder = $modifier->apply($builder);
        }

        if (! $hasLimit) {
            $builder->limit(10);
        }

        if (! $hasOffset) {
            $builder->offset(0);
        }

        if (! $hasOrderBy) {
            $builder->orderBy('id', 'desc');
        }

        return $builder;
    }

    /**
     * @param array<int, mixed> $modifiers
     */
    public function paginate(
        Builder $builder,
        array $modifiers = [],
        int $draw = 1,
    ): array {
        $total = (clone $builder)->count();

        $filteredBuilder = clone $builder;

        foreach ($modifiers as $modifier) {
            if (! $modifier instanceof QueryModifierInterface) {
                continue;
            }

            // Para contar filtered ignoramos paginación y ordenamiento
            if ($modifier instanceof QueryOptions) {
                continue;
            }

            $filteredBuilder = $modifier->apply($filteredBuilder);
        }

        $filtered = (clone $filteredBuilder)->count();

        $dataBuilder = $this(
            builder: clone $builder,
            modifiers: $modifiers,
        );

        $query = $dataBuilder->getQuery();

        $limit = max((int) ($query->limit ?? 10), 1);
        $offset = max((int) ($query->offset ?? 0), 0);

        $data = $dataBuilder->get();

        $currentPage = intdiv($offset, $limit) + 1;
        $lastPage = max(
            (int) ceil($filtered / $limit),
            1,
        );

        return [
            'data' => $data,

            'total' => $total,

            'meta' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'per_page' => $limit,
                'filtered' => $filtered,
                'total' => $total,
            ],

            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
        ];
    }
}