<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final readonly class QueryFilter implements QueryModifierInterface
{
    /**
     * @param  array<int, string>|null  $fields
     */
    private function __construct(
        private string $type,
        private string $field,
        private mixed $value = null,
        private string $comparison = '=',
        private ?array $fields = null,
    ) {}

    public static function where(
        string $field,
        mixed $value,
        string $comparison = '=',
    ): self {
        return new self(
            type: 'where',
            field: $field,
            value: $value,
            comparison: $comparison,
        );
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public static function whereIn(
        string $field,
        array $values,
    ): self {
        return new self(
            type: 'where_in',
            field: $field,
            value: $values,
        );
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public static function whereNotIn(
        string $field,
        array $values,
    ): self {
        return new self(
            type: 'where_not_in',
            field: $field,
            value: $values,
        );
    }

    public static function whereBetween(
        string $field,
        mixed $from,
        mixed $to,
    ): self {
        return new self(
            type: 'where_between',
            field: $field,
            value: [$from, $to],
        );
    }

    public static function whereNull(string $field): self
    {
        return new self(
            type: 'where_null',
            field: $field,
        );
    }

    public static function whereNotNull(string $field): self
    {
        return new self(
            type: 'where_not_null',
            field: $field,
        );
    }

    /**
     * @param  array<int, string>  $fields
     */
    public static function whereAnyLike(
        array $fields,
        string $value,
    ): self {
        return new self(
            type: 'where_any_like',
            field: $fields[0] ?? '',
            value: $value,
            fields: array_values($fields),
        );
    }

    public function category(): QueryModifierCategory
    {
        return QueryModifierCategory::FILTER;
    }

    public function apply(Builder $builder): Builder
    {
        return match ($this->type) {
            'where' => $builder->where(
                $this->field,
                $this->comparison,
                $this->value,
            ),

            'where_in' => $builder->whereIn(
                $this->field,
                (array) $this->value,
            ),

            'where_not_in' => $builder->whereNotIn(
                $this->field,
                (array) $this->value,
            ),

            'where_between' => $builder->whereBetween(
                $this->field,
                (array) $this->value,
            ),

            'where_null' => $builder->whereNull(
                $this->field,
            ),

            'where_not_null' => $builder->whereNotNull(
                $this->field,
            ),

            'where_any_like' => $this->applyWhereAnyLike($builder),

            default => throw new InvalidArgumentException(
                "Filtro no soportado: {$this->type}",
            ),
        };
    }

    private function applyWhereAnyLike(Builder $builder): Builder
    {
        $fields = $this->fields ?? [];
        $term = '%'.((string) $this->value).'%';

        if ($fields === []) {
            return $builder;
        }

        return $builder->where(function (Builder $query) use ($fields, $term): void {
            foreach ($fields as $index => $field) {
                if ($index === 0) {
                    $query->where($field, 'like', $term);

                    continue;
                }

                $query->orWhere($field, 'like', $term);
            }
        });
    }
}
