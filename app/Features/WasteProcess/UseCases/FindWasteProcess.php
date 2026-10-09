<?php

namespace App\Features\WasteProcess\UseCases;

use App\Features\WasteProcess\Dto\WasteProcess as DtoWasteProcess;
use App\Features\WasteProcess\Helpers\BuildWasteProcess;
use App\Features\WasteProcess\Queries\WasteProcessQueryBuilder;
use App\Models\WasteProcess;
use Illuminate\Support\Facades\DB;

class FindWasteProcess
{
    private $buildWasteProcess;
    public function __construct(BuildWasteProcess $buildWasteProcess)
    {
        $this->buildWasteProcess = $buildWasteProcess;
    }
    public function __invoke(int $wasteProcessId): DtoWasteProcess
    {
        return $this->buildWasteProcess->__invoke(
            (new WasteProcessQueryBuilder())->__invoke()
            ->where('id', $wasteProcessId)
            ->first()
        );
    }
}
