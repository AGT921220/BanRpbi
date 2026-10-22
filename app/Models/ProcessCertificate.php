<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessCertificate extends Model
{
    protected $fillable = [
        'waste_process_id',
        'client_id',
        'manifest_id',
        'manifest_ids',
        'manifest_folios',
        'date',
    ];

    public function wasteProcess(): BelongsTo
    {
        return $this->belongsTo(WasteProcess::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function folios(): HasMany
    {
        return $this->hasMany(ProcessFolio::class);
    }
}
