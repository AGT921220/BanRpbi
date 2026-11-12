<?php

namespace App\Http\Controllers\Dashboard;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Driver;
use App\Models\RpbiProfile;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BitacoraGeneralController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize(PermissionTypes::BITACORA_GENERAL_VIEW);

        return view('dashboard.bitacoras.general', [
            'title' => 'Bitácora General',
            'dateFrom' => $request->string('date_from')->toString(),
            'dateTo' => $request->string('date_to')->toString(),
            'dateField' => $this->dateField($request),
            'clientId' => $request->integer('client_id') ?: null,
            'status' => $request->string('status')->toString(),
            'manifestId' => $request->integer('manifest_id') ?: null,
            'driverId' => $request->integer('driver_id') ?: null,
            'profileId' => $request->integer('rpbi_profile_id') ?: null,
            'clients' => $this->clients(),
            'drivers' => $this->drivers(),
            'profiles' => $this->profiles(),
            'statuses' => $this->statuses(),
        ]);
    }

    private function dateField(Request $request): string
    {
        $dateField = $request->string('date_field')->toString();

        if (in_array($dateField, ['service_date', 'collected_at', 'warehouse_entry_at'], true)) {
            return $dateField;
        }

        return 'service_date';
    }

    /**
     * @return Collection<int, Client>
     */
    private function clients(): Collection
    {
        return Client::query()
            ->orderBy('company')
            ->orderBy('name')
            ->get(['id', 'company', 'name', 'parentarl_surname']);
    }

    /**
     * @return Collection<int, Driver>
     */
    private function drivers(): Collection
    {
        return Driver::query()
            ->orderBy('name')
            ->orderBy('parentarl_surname')
            ->get(['id', 'name', 'parentarl_surname', 'maternal_surname']);
    }

    /**
     * @return Collection<int, RpbiProfile>
     */
    private function profiles(): Collection
    {
        return RpbiProfile::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    /**
     * @return array<string, string>
     */
    private function statuses(): array
    {
        $statuses = [
            Service::STATUS_PENDING,
            Service::STATUS_SCHEDULED,
            Service::STATUS_COLLECTED,
            Service::STATUS_IN_PROCESS,
            Service::STATUS_CLOSED,
        ];

        $labels = [];

        foreach ($statuses as $status) {
            $labels[$status] = Service::statusLabel($status);
        }

        return $labels;
    }
}
