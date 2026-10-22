<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reactor extends Model
{
    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'name',
        'status',
    ];
}
