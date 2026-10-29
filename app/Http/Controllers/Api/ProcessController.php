<?php

namespace App\Http\Controllers\Api;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\WasteProcess\UseCases\CreateWasteProcess;
use App\Http\Controllers\Controller;
use App\Models\Manifest;
use App\Models\User;
use App\Models\WasteProcessType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ProcessController extends Controller
{
    public function __construct(
        private readonly CreateWasteProcess $createWasteProcess,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $typeIds = $this->creatableTypeIds($request->user());

        if ($typeIds === []) {
            abort(403, 'No tienes permiso para crear procesos.');
        }

        $data = $request->validate([
            'waste_process_type_id' => ['required', 'integer', 'in:'.implode(',', $typeIds)],
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
            if ($manifest->status === Manifest::STATUS_COLLECTED) {
                continue;
            }

            throw ValidationException::withMessages([
                'manifest_ids' => 'El manifiesto '.$manifest->id
                    .' está en estatus '.$this->statusLabel((string) $manifest->status)
                    .'. Solo se puede agregar si está Recolectado.',
            ]);
        }

        $this->ensureProcessTypes();

        $processId = ($this->createWasteProcess)(
            $manifests,
            (int) $data['waste_process_type_id'],
            $data['date_start'],
            (int) $request->user()->id,
        );

        return response()->json([
            'message' => 'Proceso '.$processId.' creado correctamente.',
            'data' => [
                'id' => $processId,
            ],
        ]);
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
            if ($user->hasPermissionTo($permission)) {
                $typeIds[] = $typeId;
            }
        }

        if ($typeIds !== []) {
            return $typeIds;
        }

        if ($user->hasPermissionTo(PermissionTypes::SERVICES_REGISTER_ENTRY)) {
            return array_values($permissions);
        }

        return [];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Manifest::STATUS_PENDING => 'Pendiente',
            Manifest::STATUS_COLLECTED => 'Recolectado',
            Manifest::STATUS_IN_PROCESS => 'En proceso',
            Manifest::STATUS_PROCESSED => 'Procesado',
            Manifest::STATUS_INVOICED => 'Facturado',
            default => 'Desconocido',
        };
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
