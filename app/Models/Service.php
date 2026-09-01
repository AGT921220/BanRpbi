<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SCHEDULED = 'scheduled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'service_date',
        'zone_id',
        'client_id',
        'contract_id',
        'driver_id',
        'status',
        'folio',
        'invoice_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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
