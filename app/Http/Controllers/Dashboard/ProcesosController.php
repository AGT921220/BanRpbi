<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\WasteProcess\Dto\WasteProcessContainer;
use App\Features\WasteProcess\Dto\WasteProcessPrar;
use App\Features\WasteProcess\Dto\WasteProcessPrs;
use App\Features\WasteProcess\Helpers\ServiceDetailBalance;
use App\Features\WasteProcess\UseCases\CreateWasteProcess;
use App\Features\WasteProcess\UseCases\FindWasteProcess;
use App\Features\WasteProcess\UseCases\FinishWasteProcess;
use App\Features\WasteProcess\UseCases\SearchProcessManifests;
use App\Features\WasteProcess\UseCases\UpdateWasteProcessHeader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Process\UpdateWasteProcessRequest;
use App\Models\Manifest;
use App\Models\Reactor;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ProcesosController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly CreateWasteProcess $createWasteProcess,
        private readonly FindWasteProcess $findWasteProcess,
        private readonly SearchProcessManifests $searchProcessManifests,
        private readonly UpdateWasteProcessHeader $updateWasteProcessHeader,
        private readonly FinishWasteProcess $finishWasteProcess,
        private readonly ServiceDetailBalance $balance,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:'
                    .PermissionTypes::PROCESS_CREATE_PRAR.'|'
                    .PermissionTypes::PROCESS_CREATE_PRS,
                only: ['create', 'store', 'edit', 'update', 'index', 'show']
            ),
        ];
    }

    public function index(): View
    {
        $processes = WasteProcess::query()
            ->with('type')
            ->orderByDesc('id')
            ->paginate(20);

        return view('dashboard.procesos.index', compact('processes'));
    }

    public function create(): View
    {
        $fechaProcess = Carbon::now()->toDateString();
        $processos = $this->creatableTypes();
        $manifests = ($this->searchProcessManifests)();

        return view('dashboard.procesos.create', compact(
            'fechaProcess',
            'processos',
            'manifests'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $processos = $this->creatableTypes();
        $data = $request->validate([
            'waste_process_type_id' => ['required', 'integer', 'in:'.$processos->pluck('id')->implode(',')],
            'date_start' => ['required', 'date'],
            'manifest_ids' => ['required', 'array', 'min:1'],
            'manifest_ids.*' => ['integer', 'distinct', 'exists:manifests,id'],
            'service_detail_ids' => ['nullable', 'array'],
            'service_detail_ids.*' => ['integer', 'distinct', 'exists:service_details,id'],
        ]);

        $manifests = Manifest::query()
            ->with(['service.serviceDetails'])
            ->whereIn('id', $data['manifest_ids'])
            ->whereNull('deleted_at')
            ->get();

        try {
            $processId = ($this->createWasteProcess)(
                $manifests,
                (int) $data['waste_process_type_id'],
                $data['date_start'],
                (int) auth()->id(),
                $data['service_detail_ids'] ?? null,
            );
        } catch (\Throwable $th) {
            report($th);

            return redirect('/procesos/create')->with('error', $th->getMessage())->withInput();
        }

        return redirect("/procesos/{$processId}/edit")->with('success', 'Proceso creado correctamente');
    }

    public function edit(int $processId): View
    {
        $processDto = ($this->findWasteProcess)($processId);
        $reactors = Reactor::query()->where('status', Reactor::STATUS_ACTIVE)->orderBy('name')->get();
        $isFinished = $processDto->isFinished();
        $typeId = $processDto->getWasteProcessTypeId();

        return view('dashboard.procesos.edit', [
            'process' => $processDto,
            'processArray' => $processDto->toArray(),
            'reactors' => $reactors,
            'isFinished' => $isFinished,
            'typeId' => $typeId,
            'isIncineracion' => $typeId === WasteProcessType::PROCESS_INCINERACION,
            'isEsterilizacion' => $typeId === WasteProcessType::PROCESS_ESTERILIZACION,
            'canFinish' => $processDto->isFinishable(),
        ]);
    }

    public function update(UpdateWasteProcessRequest $request, int $proceso): RedirectResponse
    {
        $data = $request->validated();
        $process = WasteProcess::query()->findOrFail($proceso);

        ($this->updateWasteProcessHeader)(
            $process->id,
            $data['date_start'],
            $data['hour_start_machine'] ?? '00:00',
            new WasteProcessPrar(
                isset($data['cantidad_solucion_reciclar']) ? (string) $data['cantidad_solucion_reciclar'] : null,
                isset($data['sosa_caustica']) ? (string) $data['sosa_caustica'] : null,
                isset($data['sulfactante_c500']) ? (string) $data['sulfactante_c500'] : null,
                isset($data['solucion_clarif_t42']) ? (string) $data['solucion_clarif_t42'] : null,
                isset($data['solucion_flocl_146']) ? (string) $data['solucion_flocl_146'] : null,
                isset($data['ingreso_filtro_prensa']) ? (string) $data['ingreso_filtro_prensa'] : null,
                isset($data['acido']) ? (string) $data['acido'] : null,
                isset($data['ph_inicial']) ? (string) $data['ph_inicial'] : null,
                isset($data['ph_final']) ? (string) $data['ph_final'] : null,
                isset($data['ph_lodos']) ? (string) $data['ph_lodos'] : null,
                isset($data['eca_12']) ? (string) $data['eca_12'] : null,
                isset($data['eca_22']) ? (string) $data['eca_22'] : null,
                isset($data['eca_1350']) ? (string) $data['eca_1350'] : null,
                isset($data['eca_49']) ? (string) $data['eca_49'] : null,
                isset($data['eca_20pa']) ? (string) $data['eca_20pa'] : null,
                isset($data['lodos_generados_porcentaje']) ? (string) $data['lodos_generados_porcentaje'] : null,
                isset($data['lodos_generados_tnk_lodos']) ? (string) $data['lodos_generados_tnk_lodos'] : null,
                isset($data['lodos_generados_disposicion']) ? (string) $data['lodos_generados_disposicion'] : null,
                isset($data['agua_retorno_proceso_filtro_prensa']) ? (string) $data['agua_retorno_proceso_filtro_prensa'] : null,
                isset($data['cantidad_total_agua_reciclada']) ? (string) $data['cantidad_total_agua_reciclada'] : null,
                isset($data['others']) ? (string) $data['others'] : null,
            ),
            new WasteProcessContainer(),
            new WasteProcessPrs(
                isset($data['cantidad_ingresado']) ? (string) $data['cantidad_ingresado'] : null,
                isset($data['temperatura_1']) ? (string) $data['temperatura_1'] : null,
                isset($data['temperatura_2']) ? (string) $data['temperatura_2'] : null,
                isset($data['temperatura_3']) ? (string) $data['temperatura_3'] : null,
                isset($data['cantidad_recuperados']) ? (string) $data['cantidad_recuperados'] : null,
                isset($data['porcentaje_recuperado']) ? (string) $data['porcentaje_recuperado'] : null,
                isset($data['cantidad_sedimentos']) ? (string) $data['cantidad_sedimentos'] : null,
                isset($data['porcentaje_sedimentos']) ? (string) $data['porcentaje_sedimentos'] : null,
                isset($data['factor_consumo_kw']) ? (string) $data['factor_consumo_kw'] : null,
                isset($data['consumo_kw_hrs']) ? (string) $data['consumo_kw_hrs'] : null,
                isset($data['costo_kw_hr']) ? (string) $data['costo_kw_hr'] : null,
                isset($data['costo_total_operacion']) ? (string) $data['costo_total_operacion'] : null,
            ),
            isset($data['machine_number']) ? (int) $data['machine_number'] : null,
            $data['hour_machine'] ?? null,
            $data['solution'] ?? null,
            $data['comments'] ?? null,
        );

        if (! empty($data['finish'])) {
            $details = collect($data['details'] ?? [])->map(fn (array $row) => (object) [
                'id' => (int) $row['id'],
                'value' => $row['value'],
            ]);

            ($this->finishWasteProcess)($process->id, $details, (int) auth()->id());

            return redirect()
                ->route('procesos.show', $process->id)
                ->with('success', 'Proceso cerrado correctamente.');
        }

        return redirect()
            ->route('procesos.edit', $process->id)
            ->with('success', 'Proceso actualizado correctamente.');
    }

    public function show(int $proceso): View
    {
        $process = WasteProcess::query()
            ->with([
                'type',
                'details.serviceDetail.rpbiProfile',
                'details.manifest',
                'certificates.folios',
            ])
            ->findOrFail($proceso);

        return view('dashboard.procesos.show', [
            'process' => $process,
            'balance' => $this->balance,
        ]);
    }

    public function destroy(int $proceso): RedirectResponse
    {
        abort(403, 'La eliminación de procesos no está disponible.');
    }

    private function creatableTypes(): Collection
    {
        $this->ensureProcessTypes();

        $permissions = [
            PermissionTypes::PROCESS_CREATE_PRS => WasteProcessType::PROCESS_INCINERACION,
            PermissionTypes::PROCESS_CREATE_PRAR => WasteProcessType::PROCESS_ESTERILIZACION,
        ];

        $availableProcess = [];
        foreach ($permissions as $permission => $processId) {
            if (auth()->user()->hasPermissionTo($permission)) {
                $availableProcess[] = $processId;
            }
        }

        return WasteProcessType::query()
            ->whereIn('id', $availableProcess)
            ->orderBy('id')
            ->get();
    }

    private function ensureProcessTypes(): void
    {
        $types = [
            WasteProcessType::PROCESS_INCINERACION => 'Incineración',
            WasteProcessType::PROCESS_ESTERILIZACION => 'Esterilización',
        ];

        foreach ($types as $id => $name) {
            if (WasteProcessType::query()->find($id) !== null) {
                continue;
            }

            $type = new WasteProcessType();
            $type->id = $id;
            $type->name = $name;
            $type->save();
        }
    }
}
