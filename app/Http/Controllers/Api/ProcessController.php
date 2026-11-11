<?php

namespace App\Http\Controllers\Api;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\WasteProcess\Dto\WasteProcessContainer;
use App\Features\WasteProcess\Dto\WasteProcessPrar;
use App\Features\WasteProcess\Dto\WasteProcessPrs;
use App\Features\WasteProcess\UseCases\CreateWasteProcess;
use App\Features\WasteProcess\UseCases\FindWasteProcess;
use App\Features\WasteProcess\UseCases\FinishWasteProcess;
use App\Features\WasteProcess\UseCases\UpdateWasteProcessHeader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Process\UpdateWasteProcessRequest;
use App\Models\Manifest;
use App\Models\Service;
use App\Models\User;
use App\Models\WasteProcess;
use App\Models\WasteProcessType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

final class ProcessController extends Controller
{
    public function __construct(
        private readonly CreateWasteProcess $createWasteProcess,
        private readonly FindWasteProcess $findWasteProcess,
        private readonly UpdateWasteProcessHeader $updateWasteProcessHeader,
        private readonly FinishWasteProcess $finishWasteProcess,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $typeIds = $this->creatableTypeIds($request->user());

        if ($typeIds === []) {
            abort(403, 'No tienes permiso para crear tratamientos.');
        }

        $data = $request->validate([
            'date_start' => ['required', 'date'],
            'manifest_ids' => ['required', 'array', 'min:1'],
            'manifest_ids.*' => ['integer', 'distinct', 'exists:manifests,id'],
        ]);

        $manifests = Manifest::query()
            ->with(['service.serviceDetails'])
            ->whereIn('id', $data['manifest_ids'])
            ->get();

        if ($manifests->count() !== count($data['manifest_ids'])) {
            throw ValidationException::withMessages([
                'manifest_ids' => 'No se encontró uno de los manifiestos.',
            ]);
        }

        foreach ($manifests as $manifest) {
            $status = (string) ($manifest->service?->status ?? '');

            if ($status !== Service::STATUS_COLLECTED) {
                throw ValidationException::withMessages([
                    'manifest_ids' => 'El manifiesto '.$manifest->id
                        .' está en estatus '.Service::statusLabel($status)
                        .'. Solo se puede agregar si está Recolectado.',
                ]);
            }

            if ($manifest->service?->warehouse_entry_at === null) {
                throw ValidationException::withMessages([
                    'manifest_ids' => 'El manifiesto '.$manifest->id.' no tiene entrada a almacén.',
                ]);
            }
        }

        $this->ensureProcessTypes();

        $processIds = $this->createWasteProcess->createGroupedByProfile(
            $manifests,
            $data['date_start'],
            (int) $request->user()->id,
            $typeIds,
        );

        return response()->json([
            'message' => $this->createdMessage($processIds),
            'data' => [
                'ids' => $processIds,
            ],
        ]);
    }

    /**
     * @param  list<int>  $processIds
     */
    private function createdMessage(array $processIds): string
    {
        if (count($processIds) === 1) {
            return 'Tratamiento '.$processIds[0].' creado correctamente.';
        }

        return 'Se crearon '.count($processIds).' tratamientos.';
    }

    public function index(Request $request): JsonResponse
    {
        $typeIds = $this->creatableTypeIds($request->user());

        if ($typeIds === []) {
            abort(403, 'No tienes permiso para ver tratamientos.');
        }

        $processes = WasteProcess::query()
            ->with('type')
            ->whereIn('waste_process_type_id', $typeIds)
            ->where('is_finished', false)
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (WasteProcess $process) => [
                'id' => $process->id,
                'waste_process_type_id' => (int) $process->waste_process_type_id,
                'type_name' => $process->type?->name ?? '',
                'date_start' => $process->date_start?->toDateString(),
                'is_finished' => (bool) $process->is_finished,
            ])
            ->values();

        return response()->json(['data' => $processes]);
    }

    public function show(Request $request, int $proceso): JsonResponse
    {
        $process = $this->authorizedProcess($request, $proceso);

        return response()->json([
            'data' => $this->processPayload($process->id),
        ]);
    }

    public function update(UpdateWasteProcessRequest $request, int $proceso): JsonResponse
    {
        $process = $this->authorizedProcess($request, $proceso);

        if ($process->is_finished) {
            throw ValidationException::withMessages([
                'process' => 'El tratamiento ya está cerrado.',
            ]);
        }

        $data = $request->validated();

        ($this->updateWasteProcessHeader)(
            $process->id,
            $data['date_start'],
            $data['hour_start_machine'] ?? '00:00',
            new WasteProcessPrar(
                isset($data['temp_esterilizador']) ? (string) $data['temp_esterilizador'] : null,
                isset($data['presion_camara_principal']) ? (string) $data['presion_camara_principal'] : null,
                $data['eliminacion_biologica'] ?? null,
                $data['ciclo_carga'] ?? null,
                isset($data['rpbi_bi1_cepas']) ? (string) $data['rpbi_bi1_cepas'] : null,
                isset($data['rpbi_bi2_punzocortantes']) ? (string) $data['rpbi_bi2_punzocortantes'] : null,
                isset($data['rpbi_bi4_no_anatomicos']) ? (string) $data['rpbi_bi4_no_anatomicos'] : null,
                isset($data['rpbi_bi5_sangre']) ? (string) $data['rpbi_bi5_sangre'] : null,
                isset($data['total_procesada']) ? (string) $data['total_procesada'] : null,
            ),
            new WasteProcessContainer,
            new WasteProcessPrs(
                isset($data['temp_camara_primaria']) ? (string) $data['temp_camara_primaria'] : null,
                isset($data['temp_camara_secundaria']) ? (string) $data['temp_camara_secundaria'] : null,
                isset($data['temp_entrada_venturi']) ? (string) $data['temp_entrada_venturi'] : null,
                isset($data['temp_salida_venturi']) ? (string) $data['temp_salida_venturi'] : null,
                isset($data['temp_salida_chimenea']) ? (string) $data['temp_salida_chimenea'] : null,
                isset($data['oxigeno']) ? (string) $data['oxigeno'] : null,
                isset($data['presion_negativa']) ? (string) $data['presion_negativa'] : null,
                isset($data['consumo_gas_lp']) ? (string) $data['consumo_gas_lp'] : null,
                isset($data['consumo_promedio_agua']) ? (string) $data['consumo_promedio_agua'] : null,
                isset($data['evaporacion_agua_tratada']) ? (string) $data['evaporacion_agua_tratada'] : null,
                isset($data['patologicos']) ? (string) $data['patologicos'] : null,
                isset($data['cultivos_cepas']) ? (string) $data['cultivos_cepas'] : null,
                isset($data['sangre']) ? (string) $data['sangre'] : null,
                isset($data['ceniza_generada']) ? (string) $data['ceniza_generada'] : null,
                isset($data['descarga_lavador']) ? (string) $data['descarga_lavador'] : null,
            ),
            isset($data['machine_number']) ? (int) $data['machine_number'] : null,
            $data['hour_machine'] ?? null,
            $data['solution'] ?? null,
            $data['comments'] ?? null,
        );

        if (! empty($data['finish'])) {
            $details = $process->details()
                ->where('is_finished', false)
                ->get()
                ->map(fn ($detail) => (object) [
                    'id' => (int) $detail->id,
                    'value' => $detail->total_weight,
                ]);

            ($this->finishWasteProcess)($process->id, $details, (int) $request->user()->id);

            return response()->json([
                'message' => 'Tratamiento cerrado correctamente.',
                'data' => $this->processPayload($process->id),
            ]);
        }

        return response()->json([
            'message' => 'Tratamiento actualizado correctamente.',
            'data' => $this->processPayload($process->id),
        ]);
    }

    private function authorizedProcess(Request $request, int $processId): WasteProcess
    {
        $process = WasteProcess::query()->find($processId);

        if ($process === null) {
            abort(404, 'No se encontró el tratamiento.');
        }

        $typeIds = $this->creatableTypeIds($request->user());

        if (! in_array((int) $process->waste_process_type_id, $typeIds, true)) {
            abort(403, 'No tienes permiso para este tratamiento.');
        }

        return $process;
    }

    /**
     * @return array<string, mixed>
     */
    private function processPayload(int $processId): array
    {
        $dto = ($this->findWasteProcess)($processId);
        $data = $dto->toArray();
        $typeId = (int) $data['waste_process_type_id'];

        $data['can_finish'] = $dto->isFinishable();
        $data['type_name'] = $typeId === WasteProcessType::PROCESS_INCINERACION
            ? 'Incineración'
            : 'Esterilización';
        $data['is_incineracion'] = $typeId === WasteProcessType::PROCESS_INCINERACION;
        $data['is_esterilizacion'] = $typeId === WasteProcessType::PROCESS_ESTERILIZACION;
        $data['date_start'] = Carbon::parse($data['date_start'])->toDateString();

        return $data;
    }

    /**
     * @return list<int>
     */
    private function creatableTypeIds(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $permissions = [
            PermissionTypes::PROCESS_CREATE_PRS => WasteProcessType::PROCESS_INCINERACION,
            PermissionTypes::PROCESS_CREATE_PRAR => WasteProcessType::PROCESS_ESTERILIZACION,
        ];

        $typeIds = [];
        foreach ($permissions as $permission => $typeId) {
            if ($this->allows($user, $permission)) {
                $typeIds[] = $typeId;
            }
        }

        if ($typeIds !== []) {
            return $typeIds;
        }

        if ($this->allows($user, PermissionTypes::SERVICES_REGISTER_ENTRY)) {
            return array_values($permissions);
        }

        return [];
    }

    private function allows(User $user, string $permission): bool
    {
        $exists = Permission::query()
            ->where('name', $permission)
            ->where('guard_name', 'web')
            ->exists();

        if (! $exists) {
            return false;
        }

        return $user->hasPermissionTo($permission);
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

            $type = new WasteProcessType;
            $type->id = $id;
            $type->name = $name;
            $type->save();
        }
    }
}
