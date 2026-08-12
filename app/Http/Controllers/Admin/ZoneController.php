<?php

namespace App\Http\Controllers\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Zones\Application\CreateZone;
use App\Features\Zones\Application\DeleteZone;
use App\Features\Zones\Application\ListZones;
use App\Features\Zones\Application\ToggleZoneStatus;
use App\Features\Zones\Application\UpdateZone;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreZoneRequest;
use App\Http\Requests\Admin\UpdateZoneRequest;
use App\Models\Zone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class ZoneController extends Controller
{
    public function __construct(
        private readonly ListZones $listZones,
        private readonly CreateZone $createZone,
        private readonly UpdateZone $updateZone,
        private readonly DeleteZone $deleteZone,
        private readonly ToggleZoneStatus $toggleZoneStatus,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize(PermissionTypes::ZONES_VIEW);

        $zones = ($this->listZones)(
            search: $request->filled('search')
                ? $request->string('search')->toString()
                : null,
        );

        return view('zones.index', [
            'zones' => $zones,
        ]);
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::ZONES_CREATE);

        return view('zones.create', [
            'existingZones' => $this->listZones->forMapOverlay(),
        ]);
    }

    public function store(StoreZoneRequest $request): RedirectResponse
    {
        $this->authorize(PermissionTypes::ZONES_CREATE);

        ($this->createZone)($request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona creada correctamente.');
    }

    public function edit(Zone $zone): View
    {
        $this->authorize(PermissionTypes::ZONES_UPDATE);

        return view('zones.edit', [
            'zone' => $zone,
            'existingZones' => $this->listZones->forMapOverlay($zone->id),
        ]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone): RedirectResponse
    {
        $this->authorize(PermissionTypes::ZONES_UPDATE);

        ($this->updateZone)($zone, $request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(Zone $zone): RedirectResponse
    {
        $this->authorize(PermissionTypes::ZONES_DELETE);

        try {
            ($this->deleteZone)($zone);
        } catch (Throwable $exception) {
            return redirect()
                ->route('zones.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona eliminada correctamente.');
    }

    public function toggleStatus(Zone $zone): RedirectResponse
    {
        $this->authorize(PermissionTypes::ZONES_UPDATE);

        $zone = ($this->toggleZoneStatus)($zone);

        $message = $zone->is_active
            ? 'Zona activada correctamente.'
            : 'Zona desactivada correctamente.';

        return redirect()
            ->route('zones.index')
            ->with('success', $message);
    }
}
