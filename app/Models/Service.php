<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    public const STATUS_PENDING = 'pending';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
