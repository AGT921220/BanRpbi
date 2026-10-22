<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDetail extends Model
{
    protected $table = 'service_details';

    protected $fillable = [
        'service_id',
        'weight',
        'rpbi_profile_id',
    ];

    public function rpbiProfile(): BelongsTo
    {
        return $this->belongsTo(RpbiProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
