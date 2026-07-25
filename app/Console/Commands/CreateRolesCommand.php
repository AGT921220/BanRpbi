<?php

namespace App\Console\Commands;

use App\Features\Permissions\Constants\PermissionTypes;
use Illuminate\Console\Command;
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

            $superAdministrator = Role::findOrCreate(
                'Super Administrador',
                'web'
            );

            $director = Role::findOrCreate(
                'Director',
                'web'
            );

            $sales = Role::findOrCreate(
                'Ventas',
                'web'
            );

            $logistics = Role::findOrCreate(
                'Logística',
                'web'
            );

            $driver = Role::findOrCreate(
                'Chofer',
                'web'
            );

            $billing = Role::findOrCreate(
                'Facturación',
                'web'
            );

            $query = Role::findOrCreate(
                'Consulta',
                'web'
            );

            $superAdministrator->syncPermissions(
                Permission::query()
                    ->where('guard_name', 'web')
                    ->get()
            );

            $director->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::CLIENTS_VIEW,
                PermissionTypes::CONTRACTS_VIEW,
                PermissionTypes::APPROVALS_VIEW,
                PermissionTypes::APPROVALS_APPROVE,
                PermissionTypes::APPROVALS_REJECT,
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
            ]);

            $sales->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::CLIENTS_VIEW,
                PermissionTypes::CLIENTS_CREATE,
                PermissionTypes::CLIENTS_UPDATE,
                PermissionTypes::CONTRACTS_VIEW,
                PermissionTypes::CONTRACTS_CREATE,
                PermissionTypes::CONTRACTS_UPDATE,
                PermissionTypes::CONTRACTS_RENEW,
                PermissionTypes::APPROVALS_VIEW,
                PermissionTypes::ZONES_VIEW,
                PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
                PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
                PermissionTypes::PROFILE_VIEW,
                PermissionTypes::PROFILE_UPDATE,
            ]);

            $logistics->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::COLLECTIONS_VIEW,
                PermissionTypes::COLLECTIONS_CREATE,
                PermissionTypes::COLLECTIONS_UPDATE,
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
            ]);

            $driver->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::COLLECTIONS_VIEW,
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
            ]);

            $billing->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::CLIENTS_VIEW,
                PermissionTypes::MANIFESTS_VIEW,
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
            ]);

            $query->syncPermissions([
                PermissionTypes::DASHBOARD_VIEW,
                PermissionTypes::CLIENTS_VIEW,
                PermissionTypes::CONTRACTS_VIEW,
                PermissionTypes::COLLECTIONS_VIEW,
                PermissionTypes::ROUTES_VIEW,
                PermissionTypes::MANIFESTS_VIEW,
                PermissionTypes::CERTIFICATES_VIEW,
                PermissionTypes::LOGBOOKS_VIEW,
                PermissionTypes::REPORTS_VIEW,
                PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
                PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,
                PermissionTypes::PROFILE_VIEW,
                PermissionTypes::PROFILE_UPDATE,
            ]);

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
}
