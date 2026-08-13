<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    protected $table = 'manifests';
    public const STATUS_PENDING = 'pending';
}
