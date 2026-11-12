<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            if ($service->status !== self::STATUS_COLLECTED || $service->collected_at !== null) {
                return;
            }

            $service->collected_at = now();
        });
    }

    public const STATUS_PENDING = 'pending';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COLLECTED = 'collected';

    public const STATUS_IN_PROCESS = 'in_process';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_NEXT = 'next';

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
        'collected_at',
        'folio',
        'invoice_id',
        'warehouse_entry_at',
        'warehouse_entry_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'collected_at' => 'datetime',
            'warehouse_entry_at' => 'datetime',
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

    public function manifest(): HasOne
    {
        return $this->hasOne(Manifest::class);
    }

    public function warehouseEntryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warehouse_entry_user_id');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_SCHEDULED => 'Programado',
            self::STATUS_COLLECTED => 'Recolectado',
            self::STATUS_IN_PROCESS => 'Tratamiento',
            self::STATUS_CLOSED => 'Cerrado',
            self::STATUS_NEXT => 'Siguiente',
            default => 'Desconocido',
        };
    }

    public static function pickupIsDone(string $status): bool
    {
        return in_array($status, [
            self::STATUS_COLLECTED,
            self::STATUS_IN_PROCESS,
            self::STATUS_CLOSED,
        ], true);
    }
}
