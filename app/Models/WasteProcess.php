<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteProcess extends Model
{
    use SoftDeletes;

    protected $table = 'waste_processes';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'is_finished' => 'boolean',
            'finished_at' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(WasteProcessDetail::class);
    }

    public function wasteProcessDetails(): HasMany
    {
        return $this->details();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(WasteProcessType::class, 'waste_process_type_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(ProcessCertificate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
