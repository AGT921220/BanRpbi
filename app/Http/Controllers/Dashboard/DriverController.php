<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Drivers\Application\CreateDriver;
use App\Features\Drivers\Application\DeleteDriver;
use App\Features\Drivers\Application\UpdateDriver;
use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\Constants\RoleTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDriverRequest;
use App\Http\Requests\Admin\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

final class DriverController extends Controller
{
    public function __construct(
        private readonly CreateDriver $createDriver,
        private readonly UpdateDriver $updateDriver,
        private readonly DeleteDriver $deleteDriver,
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::DRIVERS_VIEW);

        return view('drivers.index');
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::DRIVERS_CREATE);

        return view('drivers.create', [
            'zones' => $this->zonesForForm(),
            'users' => $this->usersForForm(),
        ]);
    }

    public function store(StoreDriverRequest $request): RedirectResponse
    {
        $this->authorize(PermissionTypes::DRIVERS_CREATE);

        ($this->createDriver)($request->validated());

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Chofer creado correctamente.');
    }

    public function edit(Driver $driver): View
    {
        $this->authorize(PermissionTypes::DRIVERS_UPDATE);

        return view('drivers.edit', [
            'driver' => $driver,
            'zones' => $this->zonesForForm($driver->zone_id),
            'users' => $this->usersForForm($driver->user_id),
        ]);
    }

    public function update(UpdateDriverRequest $request, Driver $driver): RedirectResponse
    {
        $this->authorize(PermissionTypes::DRIVERS_UPDATE);

        ($this->updateDriver)($driver, $request->validated());

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Chofer actualizado correctamente.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $this->authorize(PermissionTypes::DRIVERS_DELETE);

        ($this->deleteDriver)($driver);

        return redirect()
            ->route('drivers.index')
            ->with('success', 'Chofer eliminado correctamente.');
    }

    /**
     * @return Collection<int, Zone>
     */
    private function zonesForForm(?int $currentZoneId = null)
    {
        return Zone::query()
            ->where(function ($query) use ($currentZoneId): void {
                $query->where('is_active', true);

                if ($currentZoneId !== null) {
                    $query->orWhere('id', $currentZoneId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return Collection<int, User>
     */
    private function usersForForm(?int $currentUserId = null)
    {
        return User::query()
            ->whereHas('roles', function ($query): void {
                $query->where('name', RoleTypes::CHOFER);
            })
            ->where(function ($query) use ($currentUserId): void {
                $query->whereDoesntHave('driver');

                if ($currentUserId !== null) {
                    $query->orWhere('id', $currentUserId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
