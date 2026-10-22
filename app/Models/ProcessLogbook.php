<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessLogbook extends Model
{
    protected $fillable = [
        'waste_process_id',
        'user_id',
        'action',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function wasteProcess(): BelongsTo
    {
        return $this->belongsTo(WasteProcess::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
