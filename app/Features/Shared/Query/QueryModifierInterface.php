<?php

namespace App\Features\Shared\Query;

use Illuminate\Database\Eloquent\Builder;

interface QueryModifierInterface
{
    public function apply(Builder $builder): Builder;
}
