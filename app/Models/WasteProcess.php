<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteProcess extends Model
{
    protected $table = 'waste_processes';

    public function details(): HasMany
    {
        return $this->hasMany(WasteProcessDetail::class);
    }
}
