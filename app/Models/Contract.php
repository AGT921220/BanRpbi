<?php

namespace App\Models;

use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    /** @use HasFactory<ContractFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    public const FREQUENCIES = [
        'weekly',
        'biweekly',
        'monthly',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'notes',
        'duration_months',
        'frequency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function frequencyLabels(): array
    {
        return [
            'weekly' => 'Semanal',
            'biweekly' => 'Quincenal',
            'monthly' => 'Mensual',
        ];
    }
}
