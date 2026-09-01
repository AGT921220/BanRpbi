<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Services\Application\AssignServicesToDriver;
use App\Features\Services\Application\ListServicesForAssignment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignServicesToDriverRequest;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class ServiceController extends Controller
{
    public function __construct(
        private readonly ListServicesForAssignment $listServicesForAssignment,
        private readonly AssignServicesToDriver $assignServicesToDriver,
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::COLLECTIONS_VIEW);

        return view('dashboard.services.index');
    }

    public function assign(Request $request): View
    {
        $this->authorize(PermissionTypes::COLLECTIONS_ASSIGN);

        $serviceDate = $this->resolveServiceDate($request->query('date'));

        return view('dashboard.services.assign', [
            'serviceDate' => $serviceDate,
            'services' => ($this->listServicesForAssignment)($serviceDate),
            'drivers' => Driver::query()
                ->orderBy('name')
                ->orderBy('parentarl_surname')
                ->get(['id', 'name', 'parentarl_surname', 'maternal_surname']),
        ]);
    }

    public function storeAssignment(AssignServicesToDriverRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $assigned = ($this->assignServicesToDriver)(
            (int) $validated['driver_id'],
            array_map('intval', $validated['service_ids']),
        );

        return redirect()
            ->route('services.assign', ['date' => $validated['service_date']])
            ->with('success', $assigned === 1
                ? '1 recolección asignada correctamente.'
                : "{$assigned} recolecciones asignadas correctamente.");
    }

    private function resolveServiceDate(mixed $date): string
    {
        if (is_string($date) && $date !== '') {
            try {
                return Carbon::parse($date)->toDateString();
            } catch (\Throwable) {
                //
            }
        }

        return Carbon::now()->toDateString();
    }
}
