<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteProcessType extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public const PROCESS_INCINERACION = 1;
    public const PROCESS_ESTERILIZACION = 2;
}
