<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RpbiProfile extends Model
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

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(
            Contract::class,
            'contract_rpbi_profiles',
            'rpbi_profile_id',
            'contract_id',
        )->withTimestamps();
    }
}
