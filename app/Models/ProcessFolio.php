<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessFolio extends Model
{
    protected $fillable = [
        'client_id',
        'process_certificate_id',
        'waste_process_id',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(ProcessCertificate::class, 'process_certificate_id');
    }

    public function wasteProcess(): BelongsTo
    {
        return $this->belongsTo(WasteProcess::class);
    }
}
