<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\WasteProcess\UseCases\CreateWasteProcess;
use App\Features\WasteProcess\UseCases\FindWasteProcess;
use App\Features\WasteProcess\UseCases\SearchProcessManifests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Process\EditWasteProcess;
use App\Models\Manifest;
use App\Models\Reactor;
use App\Models\WasteProcessType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcesosController extends Controller implements HasMiddleware
{
    private CreateWasteProcess $createWasteProcess;
    private FindWasteProcess $findWasteProcess;
    private SearchProcessManifests $searchProcessManifests;

    public function __construct(
        CreateWasteProcess $createWasteProcess,
        FindWasteProcess $findWasteProcess,
        SearchProcessManifests $searchProcessManifests
    ) {
        $this->createWasteProcess = $createWasteProcess;
        $this->findWasteProcess = $findWasteProcess;
        $this->searchProcessManifests = $searchProcessManifests;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:'
                    .PermissionTypes::PROCESS_CREATE_PRAR.'|'
                    .PermissionTypes::PROCESS_CREATE_PRS.'|'
                    .PermissionTypes::PROCESS_CREATE_CONTAINERS,
                only: ['create', 'store', 'edit']
            ),
        ];
    }
    public function create()
    {
        // if (!(new PermissionHelper())->canCreateProcess()) {
        //     return back()->with('error', 'No tienes permisos para acceder a esta sección');
        // }
        $fechaProcess = Carbon::now()->toDateString();
        $processos = $this->creatableTypes();
        $manifests = ($this->searchProcessManifests)();

        return view('dashboard.procesos.create', compact(
            'fechaProcess',
            'processos',
            'manifests'
        ));
    }

    public function store(Request $request)
    {
        $processos = $this->creatableTypes();
        $data = $request->validate([
            'waste_process_type_id' => ['required', 'integer', 'in:'.$processos->pluck('id')->implode(',')],
            'date_start' => ['required', 'date'],
            'manifest_ids' => ['required', 'array', 'min:1'],
            'manifest_ids.*' => ['integer', 'distinct', 'exists:manifests,id'],
        ]);

        $manifests = Manifest::query()
            ->with(['service.serviceDetails'])
            ->whereIn('id', $data['manifest_ids'])
            ->whereNull('deleted_at')
            ->get();

        try {
            $processId = DB::transaction(function () use ($manifests, $data) {
                return $this->createWasteProcess->__invoke(
                    $manifests,
                    (int) $data['waste_process_type_id'],
                    $data['date_start'],
                    auth()->user()->id
                );
            });
        } catch (\Throwable $th) {
            report($th);

            return redirect('/procesos/create')->with('error', $th->getMessage())->withInput();
        }

        return redirect("/procesos/$processId/edit")->with('success', 'Proceso creado correctamente');
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
            $type = WasteProcessType::query()->find($id);
            if ($type !== null) {
                continue;
            }

            $type = new WasteProcessType();
            $type->id = $id;
            $type->name = $name;
            $type->save();
        }
    }

    public function edit(int $processId)
    {

        app(EditWasteProcess::class);
        $process = $this->findWasteProcess->__invoke($processId);
        $reactors = Reactor::where('status', Reactor::STATUS_ACTIVE)->get();
        $isFinished = $process->isFinished();

        // return $process->getDetails();
        if ($process->getWasteProcessTypeId() == WasteProcessType::PROCESS_INCINERACION) {
            return view('dashboard.procesos.edit_incineracion', compact('process', 'isFinished', 'reactors'));
        }

        if ($process->getWasteProcessTypeId() == WasteProcessType::PROCESS_ESTERILIZACION) {
            return view('dashboard.procesos.edit_esterilizacion', compact('process', 'reactors', 'isFinished'));
        }

        return $process->toArray();
        // return view('dashboard.procesos.edit', compact('process'));
    }


    // private function getManifestDetailErrors(Collection $manifests, array $manifestDetailIds): array
    // {
    //     $invalidManifestDetailIds = [];
    //     foreach ($manifests as $manifest) {
    //         $missingDetails = array_diff($manifest->manifestDetails->pluck('id')->toArray(), $manifestDetailIds);
    //         if (count($missingDetails) > 0) {
    //             $selectedDetails = implode(',', array_intersect(
    //                 $manifestDetailIds,
    //                 $manifest->manifestDetails->pluck('id')->toArray()
    //             ));

    //             $invalidManifestDetailIds[] = "<h4>Manifiesto: $manifest->folio</h4><p>Detalles Seleccionados: "
    //                 . "$selectedDetails</p><p>Detalles Faltantes: " . implode(',', $missingDetails) . "</p>";
    //         }
    //     }
    //     return $invalidManifestDetailIds;
    // }
}
