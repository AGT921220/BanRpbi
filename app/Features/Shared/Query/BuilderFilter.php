<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

final class BuilderFilter
{
    /**
     * @param  array<int, mixed>  $modifiers
     */
    public function __invoke(
        Builder $builder,
        array $modifiers = [],
        ?QueryModifierCategory $category = null,
    ): Builder {
        foreach ($modifiers as $modifier) {
            if (! $modifier instanceof QueryModifierInterface) {
                continue;
            }

            if (
                $category !== null
                && $modifier->category() !== $category
            ) {
                continue;
            }

            $builder = $modifier->apply($builder);
        }

        return $builder;
    }
}
