<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientConfigurationApproval extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'client_id',
        'client_contract_id',
        'user_id',
        'role_name',
        'approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientContract(): BelongsTo
    {
        return $this->belongsTo(ClientContract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
