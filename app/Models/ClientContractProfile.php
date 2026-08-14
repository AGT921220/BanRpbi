<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContractProfile extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_contract_id',
        'rpbi_profile_id',
    ];


    public function clientContract(): BelongsTo
    {
        return $this->belongsTo(ClientContract::class);
    }

    public function rpbiProfile(): BelongsTo
    {
        return $this->belongsTo(RpbiProfile::class, 'rpbi_profile_id');
    }
}
