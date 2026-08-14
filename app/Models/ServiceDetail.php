<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceDetail extends Model
{
    //
    protected $table = 'service_details';

    public function rpbiProfile(): BelongsTo
    {
        return $this->belongsTo(RpbiProfile::class);
    }
}
