<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    public const STATUS_CONFIGURATION_PENDING = 'configuration_pending';

    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'parentarl_surname',
        'email',
        'phone',
        'company',
        'nra',
        'rfc',
        'street',
        'num_ext',
        'num_int',
        'postal_code',
        'colony',
        'state_id',
        'city_id',
        'maps_url',
        'maps_place_id',
        'latitude',
        'longitude',
        'zone_id',
        'configuration_status',
        'configuration_submitted_at',
        'configuration_reviewed_at',
        'configuration_rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'configuration_submitted_at' => 'datetime',
            'configuration_reviewed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContract::class);
    }

    public function pendingContract(): HasOne
    {
        return $this->hasOne(ClientContract::class)
            ->where('status', ClientContract::STATUS_PENDING)
            ->latestOfMany();
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(ClientContract::class)
            ->where('status', ClientContract::STATUS_ACTIVE)
            ->latestOfMany();
    }

    public function vigenteContracts(): HasMany
    {
        return $this->hasMany(ClientContract::class)->vigente();
    }

    /**
     * @deprecated Use pendingContract() or activeContract()
     */
    public function clientContract(): HasOne
    {
        return $this->pendingContract();
    }

    public function configurationApprovals(): HasMany
    {
        return $this->hasMany(ClientConfigurationApproval::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function isConfigurable(): bool
    {
        if ($this->configuration_status === self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        if ($this->hasActiveVigenteContract()) {
            return false;
        }

        return in_array($this->configuration_status, [
            self::STATUS_CONFIGURATION_PENDING,
            self::STATUS_REJECTED,
            self::STATUS_APPROVED,
        ], true);
    }

    public function hasActiveVigenteContract(): bool
    {
        if (array_key_exists('has_vigente_contract', $this->attributes)) {
            return (int) $this->attributes['has_vigente_contract'] === 1;
        }

        return $this->contracts()->vigente()->exists();
    }

    public function fullName(): string
    {
        return trim("{$this->name} {$this->parentarl_surname}");
    }
}
