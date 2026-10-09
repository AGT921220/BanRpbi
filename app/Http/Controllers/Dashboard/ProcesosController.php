<?php

namespace App\Http\Controllers\Dashboard;

use App\Destination;
use App\Features\Shared\PermissionHelper;
use App\Features\WasteProcess\UseCases\CreateWasteProcess;
use App\Features\WasteProcess\UseCases\FindWasteProcess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Process\EditWasteProcess;
use App\Manifest;
use App\Models\Reactor;
use App\Models\WasteProcessDetail;
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

    public function __construct(
        CreateWasteProcess $createWasteProcess,
        FindWasteProcess $findWasteProcess
    ) {
        $this->createWasteProcess = $createWasteProcess;
        $this->findWasteProcess = $findWasteProcess;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:process.create_prar|process.create_prs|process.create_containers',
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

        $permissions = [
            'process.create_prar' => 1,
            'process.create_containers' => 2,
            'process.create_prs' => 3
        ];

        $availableProcess = [];
        foreach ($permissions as $permission => $processId) {
            if (auth()->user()->hasPermissionTo($permission)) {
                $availableProcess[] = $processId;
            }
        }
        $processos = WasteProcessType::whereIn('id', $availableProcess)->get();
        $destinos = Destination::where('is_process', Destination::IS_PROCESS)
            ->whereIn('process_id', $availableProcess)
            ->get();

        return view('dashboard.procesos.create', compact(
            'fechaProcess',
            'destinos',
            'processos'
        ));
    }

    public function store(Request $request)
    {
        $manifestIds = json_decode($request->input('manifest_ids'));
        // dd($manifestIds);
        $manifestDetailIds = json_decode($request->input('detail_manifest_ids'));
        $manifests = Manifest::select('id', 'service_id', 'manejo_id', 'folio')
            ->with(['manifestDetails' => function ($query) use ($manifestDetailIds) {
                $query->select('id', 'manifest_id', 'client_profile_id', 'service_detail_id')
                    ->whereIn('id', $manifestDetailIds)
                    ->whereHas('serviceDetail', function ($query) {
                        $query->whereNull('cancelled_at');
                    })
                    ->with('serviceDetail')
                    ->with('clientProfile')
                    ->whereDoesntHave('wasteProcessDetail', function ($query) {
                        $query->where('is_completed', WasteProcessDetail::IS_COMPLETED);
                    });
            }])
            ->whereIn('id', $manifestIds)
            ->get();

        // dd($manifests->toArray());
        // $manifestDetailErrors = $this->getManifestDetailErrors($manifests, $manifestDetailIds);

        // if (count($manifestDetailErrors) > 0) {
        //     return redirect('/salidas/create')
        //         ->with('errorHtml', "<h3>Faltaron agregar perfiles</h3><br><ul><li>" . implode(
        //             '</li><li>',
        //             $manifestDetailErrors
        //         ) . "</li></ul>");
        // }
        try {
            DB::beginTransaction();
            $processId = $this->createWasteProcess->__invoke(
                $manifests,
                $request->input('waste_process_type_id'),
                $request->input('destination_id'),
                $request->input('date_start'),
                auth()->user()->id
            );
            DB::commit();
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();
            return redirect('/procesos/create')->with('error', $th->getMessage());
        }

        return redirect("/procesos/$processId/edit")->with('success', "Proceso creado correctamente");
    }

    public function edit(int $processId)
    {

        app(EditWasteProcess::class);
        $process = $this->findWasteProcess->__invoke($processId);
        $reactors = Reactor::where('status', Reactor::STATUS_ACTIVE)->get();
        $isFinished = $process->isFinished();

        // return $process->getDetails();
        if ($process->getWasteProcessTypeId() == WasteProcessType::PROCESS_PRAR) {
            return view('dashboard.procesos.edit_prar', compact('process', 'reactors', 'isFinished'));
        }

        if ($process->getWasteProcessTypeId() == WasteProcessType::PROCESS_CONTAINERS) {
            // return $process->toArray();
            return view('dashboard.procesos.edit_containers', compact('process', 'isFinished', 'reactors'));
        }
        if ($process->getWasteProcessTypeId() == WasteProcessType::PROCESS_PRS) {
            return view('dashboard.procesos.edit_prs', compact('process', 'isFinished', 'reactors'));
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
