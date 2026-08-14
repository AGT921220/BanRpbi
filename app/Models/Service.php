<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SCHEDULED = 'scheduled';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function serviceDetails(): HasMany
    {
        return $this->hasMany(ServiceDetail::class);
    }

    public function manifests(): HasMany
    {
        return $this->hasMany(Manifest::class);
    }
}
