<?php

namespace App\Features\WasteProcess\Application;

use App\Features\Shared\Query\BuilderFilter;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;

class SearchLogbookHeaders
{
    public function __construct(
        private readonly BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $options
     * @return array<string, mixed>
     */
    public function __invoke(
        string $filtro,
        string $dateFrom,
        string $dateTo,
        array $options,
        int $draw,
    ): array {
        $query = WasteProcess::query()
            ->select([
                'waste_processes.id',
                'waste_processes.date_start',
                'waste_processes.is_finished',
                'waste_process_types.name as type_name',
            ])
            ->join(
                'waste_process_types',
                'waste_process_types.id',
                '=',
                'waste_processes.waste_process_type_id',
            )
            ->with('details:id,waste_process_id,manifest_id')
            ->whereIn('waste_processes.waste_process_type_id', $this->typeIds($filtro));

        if ($dateFrom !== '') {
            $query->whereDate('waste_processes.date_start', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('waste_processes.date_start', '<=', $dateTo);
        }

        $data = $this->builderFilter->paginate(
            builder: $query,
            modifiers: $options,
            draw: $draw,
        );

        $data['data'] = $data['data']->map(function (WasteProcess $process) {
            $manifests = $process->details
                ->pluck('manifest_id')
                ->unique()
                ->filter()
                ->implode(', ');

            return [
                'id' => $process->id,
                'fecha' => $process->date_start?->format('d/m/Y') ?? '',
                'tipo' => $process->type_name ?: '—',
                'estado' => $process->is_finished ? 'Cerrado' : 'Tratamiento',
                'manifiestos' => $manifests !== '' ? $manifests : '—',
            ];
        });

        return $data;
    }

    /**
     * @return array<int, int>
     */
    private function typeIds(string $filtro): array
    {
        if ($filtro === 'incineracion') {
            return [WasteProcessType::PROCESS_INCINERACION];
        }

        if ($filtro === 'esterilizacion') {
            return [WasteProcessType::PROCESS_ESTERILIZACION];
        }

        return [
            WasteProcessType::PROCESS_INCINERACION,
            WasteProcessType::PROCESS_ESTERILIZACION,
        ];
    }
}
