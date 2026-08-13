<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manifest extends Model
{
    protected $table = 'manifests';
    public const STATUS_PENDING = 'pending';

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
