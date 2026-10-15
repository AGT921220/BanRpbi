<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteProcessDetail extends Model
{
    protected $table = 'waste_process_details';

    public const IS_COMPLETED = 1;

    public function process(): BelongsTo
    {
        return $this->belongsTo(WasteProcess::class, 'waste_process_id');
    }
}
