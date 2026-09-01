<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\Constants\RoleTypes;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class CreateRolesCommand extends Command
{
    protected $signature = 'roles:create';

    protected $description = 'Crea los roles base y asigna sus permisos';

    public function handle(
        PermissionRegistrar $permissionRegistrar
    ): int {
        try {
            $permissionRegistrar->forgetCachedPermissions();

            $admin = $this->resolveRole(RoleTypes::ADMIN);
            $ventas = $this->resolveRole(RoleTypes::VENTAS);
            $logistica = $this->resolveRole(RoleTypes::LOGISTICA);
            $chofer = $this->resolveRole(RoleTypes::CHOFER);
            $directorGeneral = $this->resolveRole(RoleTypes::DIRECTOR_GENERAL);
            $directorVentas = $this->resolveRole(RoleTypes::DIRECTOR_VENTAS, [
                'Director Ventas',
                'Director',
            ]);
            $facturacion = $this->resolveRole(RoleTypes::FACTURACION, [
                'Facturación',
            ]);
            $cliente = $this->resolveRole(RoleTypes::CLIENTE, [
                'Consulta',
            ]);

            $this->migrateApprovalRoleNames([
                'Director Ventas' => RoleTypes::DIRECTOR_VENTAS,
            ]);

            $admin->syncPermissions(
                Permission::query()
                    ->where('guard_name', 'web')
                    ->get()
            );

            $directorGeneral->syncPermissions($this->directorGeneralPermissions());
            $directorVentas->syncPermissions($this->directorVentasPermissions());
            $ventas->syncPermissions($this->ventasPermissions());
            $logistica->syncPermissions($this->logisticaPermissions());
            $chofer->syncPermissions($this->choferPermissions());
            $facturacion->syncPermissions($this->facturacionPermissions());
            $cliente->syncPermissions($this->clientePermissions());

            $permissionRegistrar->forgetCachedPermissions();

            $this->components->info(
                'Roles y permisos creados correctamente.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->components->error(
                'No fue posible crear los roles: '
                .$exception->getMessage()
            );

            return self::FAILURE;
        }
    }

    /**
     * @param  list<string>  $legacyNames
     */
    private function resolveRole(string $name, array $legacyNames = []): Role
    {
        $role = Role::findOrCreate($name, 'web');

        foreach ($legacyNames as $legacyName) {
            if ($legacyName === $name) {
                continue;
            }

            $legacy = Role::query()
                ->where('name', $legacyName)
                ->where('guard_name', 'web')
                ->first();

            if ($legacy === null) {
                continue;
            }

            User::role($legacyName)->each(function (User $user) use ($role, $legacyName): void {
                $user->removeRole($legacyName);
                $user->assignRole($role);
            });

            $legacy->delete();
            $this->components->info("Rol \"{$legacyName}\" migrado a \"{$name}\".");
        }

        return $role;
    }

    /**
     * @param  array<string, string>  $renames
     */
    private function migrateApprovalRoleNames(array $renames): void
    {
        if (! DB::getSchemaBuilder()->hasTable('client_configuration_approvals')) {
            return;
        }

        foreach ($renames as $legacyName => $newName) {
            DB::table('client_configuration_approvals')
                ->where('role_name', $legacyName)
                ->update(['role_name' => $newName]);
        }
    }

    /**
     * @return list<string>
     */
    private function directorGeneralPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CONTRACTS_VIEW,
            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::APPROVALS_REJECT,
            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
            PermissionTypes::CLIENT_CONTRACTS_REJECT,
            PermissionTypes::COLLECTIONS_VIEW,
            PermissionTypes::ROUTES_VIEW,
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::MANIFESTS_VIEW,
            PermissionTypes::ENVIRONMENTAL_PROCESSES_VIEW,
            PermissionTypes::BATCHES_VIEW,
            PermissionTypes::CERTIFICATES_VIEW,
            PermissionTypes::LOGBOOKS_VIEW,
            PermissionTypes::INVOICES_VIEW,
            PermissionTypes::PAYMENTS_VIEW,
            PermissionTypes::REPORTS_VIEW,
            PermissionTypes::REPORTS_EXPORT,
            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return list<string>
     */
    private function directorVentasPermissions(): array
    {
        return array_values(array_unique([
            ...$this->directorGeneralPermissions(),
            ...$this->ventasPermissions(),
        ]));
    }

    /**
     * @return list<string>
     */
    private function ventasPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_CREATE,
            PermissionTypes::CLIENTS_UPDATE,
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CONTRACTS_VIEW,
            PermissionTypes::CONTRACTS_CREATE,
            PermissionTypes::CONTRACTS_UPDATE,
            PermissionTypes::CONTRACTS_DELETE,
            PermissionTypes::CONTRACTS_RENEW,
            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return list<string>
     */
    private function logisticaPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::COLLECTIONS_VIEW,
            PermissionTypes::COLLECTIONS_CREATE,
            PermissionTypes::COLLECTIONS_UPDATE,
            PermissionTypes::COLLECTIONS_DELETE,
            PermissionTypes::COLLECTIONS_ASSIGN,
            PermissionTypes::DRIVERS_VIEW,
            PermissionTypes::ROUTES_VIEW,
            PermissionTypes::ROUTES_CREATE,
            PermissionTypes::ROUTES_UPDATE,
            PermissionTypes::ROUTES_ASSIGN,
            PermissionTypes::ZONES_VIEW,
            PermissionTypes::ZONES_CREATE,
            PermissionTypes::ZONES_UPDATE,
            PermissionTypes::ZONES_DELETE,
            PermissionTypes::MANIFESTS_VIEW,
            PermissionTypes::MANIFESTS_CREATE,
            PermissionTypes::MANIFESTS_UPDATE,
            PermissionTypes::MANIFESTS_DOWNLOAD,
            PermissionTypes::MANIFESTS_PRINT,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return list<string>
     */
    private function choferPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::COLLECTIONS_VIEW,
            PermissionTypes::COLLECTIONS_UPDATE,
            PermissionTypes::COLLECTIONS_COMPLETE,
            PermissionTypes::ROUTES_VIEW,
            PermissionTypes::WASTE_CAPTURE_VIEW,
            PermissionTypes::WASTE_CAPTURE_CREATE,
            PermissionTypes::WASTE_CAPTURE_UPDATE,
            PermissionTypes::WASTE_CAPTURE_UPLOAD_PHOTOS,
            PermissionTypes::DRIVER_SHIFTS_VIEW,
            PermissionTypes::DRIVER_SHIFTS_START,
            PermissionTypes::DRIVER_SHIFTS_FINISH,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return list<string>
     */
    private function facturacionPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::MANIFESTS_VIEW,
            PermissionTypes::MANIFESTS_DOWNLOAD,
            PermissionTypes::CERTIFICATES_VIEW,
            PermissionTypes::CERTIFICATES_DOWNLOAD,
            PermissionTypes::INVOICES_VIEW,
            PermissionTypes::INVOICES_CREATE,
            PermissionTypes::INVOICES_CANCEL,
            PermissionTypes::INVOICES_DOWNLOAD_PDF,
            PermissionTypes::INVOICES_DOWNLOAD_XML,
            PermissionTypes::PAYMENTS_VIEW,
            PermissionTypes::PAYMENTS_CREATE,
            PermissionTypes::PAYMENTS_UPDATE,
            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return list<string>
     */
    private function clientePermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,
            PermissionTypes::CONTRACTS_VIEW,
            PermissionTypes::MANIFESTS_VIEW,
            PermissionTypes::MANIFESTS_DOWNLOAD,
            PermissionTypes::CERTIFICATES_VIEW,
            PermissionTypes::CERTIFICATES_DOWNLOAD,
            PermissionTypes::INVOICES_VIEW,
            PermissionTypes::INVOICES_DOWNLOAD_PDF,
            PermissionTypes::INVOICES_DOWNLOAD_XML,
            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }
}
