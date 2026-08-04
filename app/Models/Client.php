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

    public function isConfigurable(): bool
    {
        if ($this->configuration_status === self::STATUS_PENDING_APPROVAL) {
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
        return $this->contracts()
            ->where('status', ClientContract::STATUS_ACTIVE)
            ->where(function ($query): void {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->exists();
    }

    public function fullName(): string
    {
        return trim("{$this->name} {$this->parentarl_surname}");
    }
}
