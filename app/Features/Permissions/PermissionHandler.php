<?php

namespace App\Features\Permissions;

use App\Features\Permissions\Constants\PermissionTypes;

class PermissionHandler
{
    /**
     * @return array<int, string>
     */
    public function getAllPermissions(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW,

            PermissionTypes::CLIENTS_VIEW,
            PermissionTypes::CLIENTS_CREATE,
            PermissionTypes::CLIENTS_UPDATE,
            PermissionTypes::CLIENTS_DELETE,
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS,
            PermissionTypes::CLIENTS_SWITCH_STATUS,

            PermissionTypes::CONTRACTS_VIEW,
            PermissionTypes::CONTRACTS_CREATE,
            PermissionTypes::CONTRACTS_UPDATE,
            PermissionTypes::CONTRACTS_DELETE,
            PermissionTypes::CONTRACTS_RENEW,

            PermissionTypes::CLIENT_CONTRACTS_APPROVE,
            PermissionTypes::CLIENT_CONTRACTS_REJECT,

            PermissionTypes::APPROVALS_VIEW,
            PermissionTypes::APPROVALS_REJECT,

            PermissionTypes::COLLECTIONS_VIEW,
            PermissionTypes::COLLECTIONS_CREATE,
            PermissionTypes::COLLECTIONS_UPDATE,
            PermissionTypes::COLLECTIONS_DELETE,
            PermissionTypes::COLLECTIONS_COMPLETE,

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

            PermissionTypes::WASTE_CAPTURE_VIEW,
            PermissionTypes::WASTE_CAPTURE_CREATE,
            PermissionTypes::WASTE_CAPTURE_UPDATE,
            PermissionTypes::WASTE_CAPTURE_UPLOAD_PHOTOS,

            PermissionTypes::DRIVER_SHIFTS_VIEW,
            PermissionTypes::DRIVER_SHIFTS_START,
            PermissionTypes::DRIVER_SHIFTS_FINISH,

            PermissionTypes::ENVIRONMENTAL_PROCESSES_VIEW,
            PermissionTypes::ENVIRONMENTAL_PROCESSES_CREATE,
            PermissionTypes::ENVIRONMENTAL_PROCESSES_UPDATE,
            PermissionTypes::ENVIRONMENTAL_PROCESSES_COMPLETE,
            PermissionTypes::ENVIRONMENTAL_PROCESSES_REPROCESS,

            PermissionTypes::BATCHES_VIEW,
            PermissionTypes::BATCHES_CREATE,
            PermissionTypes::BATCHES_UPDATE,

            PermissionTypes::CERTIFICATES_VIEW,
            PermissionTypes::CERTIFICATES_CREATE,
            PermissionTypes::CERTIFICATES_DOWNLOAD,
            PermissionTypes::CERTIFICATES_PRINT,

            PermissionTypes::LOGBOOKS_VIEW,
            PermissionTypes::LOGBOOKS_CREATE,
            PermissionTypes::LOGBOOKS_DOWNLOAD,

            PermissionTypes::INVOICES_VIEW,
            PermissionTypes::INVOICES_CREATE,
            PermissionTypes::INVOICES_CANCEL,
            PermissionTypes::INVOICES_DOWNLOAD_PDF,
            PermissionTypes::INVOICES_DOWNLOAD_XML,

            PermissionTypes::PAYMENTS_VIEW,
            PermissionTypes::PAYMENTS_CREATE,
            PermissionTypes::PAYMENTS_UPDATE,
            PermissionTypes::PAYMENTS_DELETE,

            PermissionTypes::REPORTS_VIEW,
            PermissionTypes::REPORTS_EXPORT,

            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD,

            PermissionTypes::USERS_VIEW,
            PermissionTypes::USERS_CREATE,
            PermissionTypes::USERS_UPDATE,
            PermissionTypes::USERS_DELETE,
            PermissionTypes::USERS_SWITCH_STATUS,

            PermissionTypes::ROLES_VIEW,
            PermissionTypes::ROLES_CREATE,
            PermissionTypes::ROLES_UPDATE,
            PermissionTypes::ROLES_DELETE,
            PermissionTypes::ROLES_ASSIGN_PERMISSIONS,

            PermissionTypes::CATALOGS_VIEW,
            PermissionTypes::CATALOGS_CREATE,
            PermissionTypes::CATALOGS_UPDATE,
            PermissionTypes::CATALOGS_DELETE,

            PermissionTypes::SETTINGS_VIEW,
            PermissionTypes::SETTINGS_UPDATE,

            PermissionTypes::PROFILE_VIEW,
            PermissionTypes::PROFILE_UPDATE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getPermissionNamesInSpanish(): array
    {
        return [
            PermissionTypes::DASHBOARD_VIEW => 'Ver dashboard',

            PermissionTypes::CLIENTS_VIEW => 'Ver clientes',
            PermissionTypes::CLIENTS_CREATE => 'Crear clientes',
            PermissionTypes::CLIENTS_UPDATE => 'Editar clientes',
            PermissionTypes::CLIENTS_DELETE => 'Eliminar clientes',
            PermissionTypes::CLIENTS_ASSIGN_CONTRACTS => 'Asignar contratos a clientes',
            PermissionTypes::CLIENTS_SWITCH_STATUS => 'Activar o desactivar clientes',

            PermissionTypes::CONTRACTS_VIEW => 'Ver contratos',
            PermissionTypes::CONTRACTS_CREATE => 'Crear contratos',
            PermissionTypes::CONTRACTS_UPDATE => 'Editar contratos',
            PermissionTypes::CONTRACTS_DELETE => 'Eliminar contratos',
            PermissionTypes::CONTRACTS_RENEW => 'Renovar contratos',

            PermissionTypes::CLIENT_CONTRACTS_APPROVE => 'Aprobar contratos de clientes',
            PermissionTypes::CLIENT_CONTRACTS_REJECT => 'Rechazar contratos de clientes',
            PermissionTypes::APPROVALS_VIEW => 'Ver aprobaciones',
            // PermissionTypes::APPROVALS_REJECT => 'Rechazar solicitudes',

            PermissionTypes::COLLECTIONS_VIEW => 'Ver recolecciones',
            PermissionTypes::COLLECTIONS_CREATE => 'Crear recolecciones',
            PermissionTypes::COLLECTIONS_UPDATE => 'Editar recolecciones',
            PermissionTypes::COLLECTIONS_DELETE => 'Eliminar recolecciones',
            PermissionTypes::COLLECTIONS_COMPLETE => 'Completar recolecciones',

            PermissionTypes::ROUTES_VIEW => 'Ver rutas',
            PermissionTypes::ROUTES_CREATE => 'Crear rutas',
            PermissionTypes::ROUTES_UPDATE => 'Editar rutas',
            PermissionTypes::ROUTES_ASSIGN => 'Asignar rutas',

            PermissionTypes::ZONES_VIEW => 'Ver zonas',
            PermissionTypes::ZONES_CREATE => 'Crear zonas',
            PermissionTypes::ZONES_UPDATE => 'Editar zonas',
            PermissionTypes::ZONES_DELETE => 'Eliminar zonas',

            PermissionTypes::MANIFESTS_VIEW => 'Ver manifiestos',
            PermissionTypes::MANIFESTS_CREATE => 'Crear manifiestos',
            PermissionTypes::MANIFESTS_UPDATE => 'Editar manifiestos',
            PermissionTypes::MANIFESTS_DOWNLOAD => 'Descargar manifiestos',
            PermissionTypes::MANIFESTS_PRINT => 'Imprimir manifiestos',

            PermissionTypes::WASTE_CAPTURE_VIEW => 'Ver captura de residuos',
            PermissionTypes::WASTE_CAPTURE_CREATE => 'Capturar residuos',
            PermissionTypes::WASTE_CAPTURE_UPDATE => 'Editar captura de residuos',
            PermissionTypes::WASTE_CAPTURE_UPLOAD_PHOTOS => 'Subir fotografías de residuos',

            PermissionTypes::DRIVER_SHIFTS_VIEW => 'Ver jornadas de chofer',
            PermissionTypes::DRIVER_SHIFTS_START => 'Iniciar jornada de chofer',
            PermissionTypes::DRIVER_SHIFTS_FINISH => 'Finalizar jornada de chofer',

            PermissionTypes::ENVIRONMENTAL_PROCESSES_VIEW => 'Ver procesos ambientales',
            PermissionTypes::ENVIRONMENTAL_PROCESSES_CREATE => 'Crear procesos ambientales',
            PermissionTypes::ENVIRONMENTAL_PROCESSES_UPDATE => 'Editar procesos ambientales',
            PermissionTypes::ENVIRONMENTAL_PROCESSES_COMPLETE => 'Completar procesos ambientales',
            PermissionTypes::ENVIRONMENTAL_PROCESSES_REPROCESS => 'Reprocesar ciclos ambientales',

            PermissionTypes::BATCHES_VIEW => 'Ver bachadas',
            PermissionTypes::BATCHES_CREATE => 'Crear bachadas',
            PermissionTypes::BATCHES_UPDATE => 'Editar bachadas',

            PermissionTypes::CERTIFICATES_VIEW => 'Ver certificados',
            PermissionTypes::CERTIFICATES_CREATE => 'Crear certificados',
            PermissionTypes::CERTIFICATES_DOWNLOAD => 'Descargar certificados',
            PermissionTypes::CERTIFICATES_PRINT => 'Imprimir certificados',

            PermissionTypes::LOGBOOKS_VIEW => 'Ver bitácoras',
            PermissionTypes::LOGBOOKS_CREATE => 'Crear bitácoras',
            PermissionTypes::LOGBOOKS_DOWNLOAD => 'Descargar bitácoras',

            PermissionTypes::INVOICES_VIEW => 'Ver facturas',
            PermissionTypes::INVOICES_CREATE => 'Generar facturas',
            PermissionTypes::INVOICES_CANCEL => 'Cancelar facturas',
            PermissionTypes::INVOICES_DOWNLOAD_PDF => 'Descargar PDF de factura',
            PermissionTypes::INVOICES_DOWNLOAD_XML => 'Descargar XML de factura',

            PermissionTypes::PAYMENTS_VIEW => 'Ver pagos',
            PermissionTypes::PAYMENTS_CREATE => 'Registrar pagos',
            PermissionTypes::PAYMENTS_UPDATE => 'Editar pagos',
            PermissionTypes::PAYMENTS_DELETE => 'Eliminar pagos',

            PermissionTypes::REPORTS_VIEW => 'Ver reportes',
            PermissionTypes::REPORTS_EXPORT => 'Exportar reportes',

            PermissionTypes::CUSTOMER_DOCUMENTS_VIEW => 'Ver documentos de clientes',
            PermissionTypes::CUSTOMER_DOCUMENTS_DOWNLOAD => 'Descargar documentos de clientes',

            PermissionTypes::USERS_VIEW => 'Ver usuarios',
            PermissionTypes::USERS_CREATE => 'Crear usuarios',
            PermissionTypes::USERS_UPDATE => 'Editar usuarios',
            PermissionTypes::USERS_DELETE => 'Eliminar usuarios',
            PermissionTypes::USERS_SWITCH_STATUS => 'Activar o desactivar usuarios',

            PermissionTypes::ROLES_VIEW => 'Ver roles',
            PermissionTypes::ROLES_CREATE => 'Crear roles',
            PermissionTypes::ROLES_UPDATE => 'Editar roles',
            PermissionTypes::ROLES_DELETE => 'Eliminar roles',
            PermissionTypes::ROLES_ASSIGN_PERMISSIONS => 'Asignar permisos a roles',

            PermissionTypes::CATALOGS_VIEW => 'Ver catálogos',
            PermissionTypes::CATALOGS_CREATE => 'Crear elementos de catálogo',
            PermissionTypes::CATALOGS_UPDATE => 'Editar elementos de catálogo',
            PermissionTypes::CATALOGS_DELETE => 'Eliminar elementos de catálogo',

            PermissionTypes::SETTINGS_VIEW => 'Ver configuración',
            PermissionTypes::SETTINGS_UPDATE => 'Editar configuración',

            PermissionTypes::PROFILE_VIEW => 'Ver perfil propio',
            PermissionTypes::PROFILE_UPDATE => 'Editar perfil propio',
        ];
    }

    public function resolvePermissionName(string $permission): string
    {
        return $this->getPermissionNamesInSpanish()[$permission]
            ?? $permission;
    }

    /**
     * @return array<int, array{name: string, name_in_spanish: string}>
     */
    public function getAllPermissionsWithSpanishNames(): array
    {
        $spanishNames = $this->getPermissionNamesInSpanish();

        return array_map(
            static fn (string $permission): array => [
                'name' => $permission,
                'name_in_spanish' => $spanishNames[$permission]
                    ?? $permission,
            ],
            $this->getAllPermissions()
        );
    }

    /**
     * Agrupa permisos por módulo (prefijo antes del punto).
     *
     * @return array<string, array<int, array{name: string, label: string}>>
     */
    public function getGroupedPermissions(): array
    {
        $labels = $this->getPermissionNamesInSpanish();
        $grouped = [];

        foreach ($this->getAllPermissions() as $permission) {
            $module = explode('.', $permission)[0] ?? 'otros';

            $grouped[$module][] = [
                'name' => $permission,
                'label' => $labels[$permission] ?? $permission,
            ];
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @return array<string, string>
     */
    public function getModuleLabels(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'clients' => 'Clientes',
            'contracts' => 'Contratos',
            'client-contracts' => 'Contratos de clientes',
            'approvals' => 'Aprobaciones',
            'collections' => 'Recolecciones',
            'routes' => 'Rutas',
            'zones' => 'Zonas',
            'manifests' => 'Manifiestos',
            'waste_capture' => 'Captura de residuos',
            'driver_shifts' => 'Jornadas de chofer',
            'environmental_processes' => 'Procesos ambientales',
            'batches' => 'Bachadas',
            'certificates' => 'Certificados',
            'logbooks' => 'Bitácoras',
            'invoices' => 'Facturas',
            'payments' => 'Pagos',
            'reports' => 'Reportes',
            'customer_documents' => 'Documentos de clientes',
            'users' => 'Usuarios',
            'roles' => 'Roles',
            'catalogs' => 'Catálogos',
            'settings' => 'Configuración',
            'profile' => 'Perfil',
        ];
    }
}
