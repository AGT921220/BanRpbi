<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
