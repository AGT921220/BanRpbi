<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manifest extends Model
{
    use SoftDeletes;

    protected $table = 'manifests';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COLLECTED = 'collected';

    public const STATUS_IN_PROCESS = 'in_process';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_INVOICED = 'invoiced';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'public_uuid',
        'service_id',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
