<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final readonly class QueryOptions implements QueryModifierInterface
{
    private function __construct(
        private string $type,
        private mixed $value,
        private ?string $field = null,
    ) {}

    public static function orderBy(
        string $field,
        string $direction = 'asc',
    ): self {
        return new self(
            type: 'order_by',
            value: $direction,
            field: $field,
        );
    }

    public function getType(): string
    {
        return $this->type;
    }
    public static function offset(int $offset): self
    {
        return new self(
            type: 'offset',
            value: max($offset, 0),
        );
    }

    public static function limit(int $limit): self
    {
        return new self(
            type: 'limit',
            value: max($limit, 1),
        );
    }

    public function category(): QueryModifierCategory
    {
        return QueryModifierCategory::OPTION;
    }

    public function apply(Builder $builder): Builder
    {
        return match ($this->type) {
            'order_by' => $builder->orderBy(
                (string) $this->field,
                $this->normalizeDirection(),
            ),

            'offset' => $builder->offset(
                (int) $this->value,
            ),

            'limit' => $builder->limit(
                (int) $this->value,
            ),

            default => throw new InvalidArgumentException(
                "Opción de consulta no soportada: {$this->type}",
            ),
        };
    }

    private function normalizeDirection(): string
    {
        $direction = strtolower((string) $this->value);

        return in_array($direction, ['asc', 'desc'], true)
            ? $direction
            : 'asc';
    }
}
