<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientContract extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_COMPLETED = 2;

    public const STATUS_CANCELLED = 3;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'contract_id',
        'user_id',
        'notes',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientContractProfiles(): HasMany
    {
        return $this->hasMany(ClientContractProfile::class);
    }

    public function rpbiProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            RpbiProfile::class,
            'client_contract_profiles',
            'client_contract_id',
            'rpbi_profile_id',
        )->withTimestamps();
    }
}
