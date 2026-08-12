<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProfile extends Model
{
    protected $table = 'rpbi_profiles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function clientContractProfiles(): HasMany
    {
        return $this->hasMany(ClientContractProfile::class, 'rpbi_profile_id');
    }

    public function clientContracts(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientContract::class,
            'client_contract_profiles',
            'rpbi_profile_id',
            'client_contract_id',
        )->withTimestamps();
    }
}
