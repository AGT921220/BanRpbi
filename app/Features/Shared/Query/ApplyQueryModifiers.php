<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

final class ApplyQueryModifiers
{
    /**
     * @param  array<int, mixed>  $modifiers
     */
    public function __invoke(
        Builder $builder,
        array $modifiers = [],
    ): Builder {
        foreach ($modifiers as $modifier) {
            if (! $modifier instanceof QueryModifierInterface) {
                continue;
            }

            $builder = $modifier->apply($builder);
        }

        return $builder;
    }
}
