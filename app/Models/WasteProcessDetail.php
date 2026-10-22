<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteProcessDetail extends Model
{
    protected $table = 'waste_process_details';

    public const IS_COMPLETED = 1;

    public const IS_NOT_COMPLETED = 0;

    protected $fillable = [
        'waste_process_id',
        'manifest_id',
        'service_detail_id',
        'total_weight',
        'process_weight',
        'is_finished',
        'is_completed',
        'finished_by_id',
        'finished_at',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(WasteProcess::class, 'waste_process_id');
    }

    public function serviceDetail(): BelongsTo
    {
        return $this->belongsTo(ServiceDetail::class);
    }

    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }
}
