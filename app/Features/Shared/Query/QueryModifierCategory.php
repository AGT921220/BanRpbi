<?php

namespace App\Features\Shared\Query;

enum QueryModifierCategory: string
{
    case FILTER = 'filter';
    case OPTION = 'option';
}
