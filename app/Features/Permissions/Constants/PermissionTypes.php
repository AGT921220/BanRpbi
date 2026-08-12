<?php

namespace App\Features\Permissions\Constants;

final class PermissionTypes
{
    // Dashboard
    public const DASHBOARD_VIEW = 'dashboard.view';

    // Clientes
    public const CLIENTS_VIEW = 'clients.view';
    public const CLIENTS_CREATE = 'clients.create';
    public const CLIENTS_UPDATE = 'clients.update';
    public const CLIENTS_DELETE = 'clients.delete';
    public const CLIENTS_ASSIGN_CONTRACTS = 'clients.assign_contracts';
    public const CLIENTS_SWITCH_STATUS = 'clients.switch_status';

    // Contratos
    public const CONTRACTS_VIEW = 'contracts.view';
    public const CONTRACTS_CREATE = 'contracts.create';
    public const CONTRACTS_UPDATE = 'contracts.update';
    public const CONTRACTS_DELETE = 'contracts.delete';
    public const CONTRACTS_RENEW = 'contracts.renew';

    // Contratos de clientes
    public const CLIENT_CONTRACTS_APPROVE = 'client_contracts.approve';
    public const CLIENT_CONTRACTS_REJECT = 'client_contracts.reject';

    // Aprobaciones
    public const APPROVALS_VIEW = 'approvals.view';
    public const APPROVALS_REJECT = 'approvals.reject';

    // Recolecciones
    public const COLLECTIONS_VIEW = 'collections.view';
    public const COLLECTIONS_CREATE = 'collections.create';
    public const COLLECTIONS_UPDATE = 'collections.update';
    public const COLLECTIONS_DELETE = 'collections.delete';
    public const COLLECTIONS_COMPLETE = 'collections.complete';

    // Rutas
    public const ROUTES_VIEW = 'routes.view';
    public const ROUTES_CREATE = 'routes.create';
    public const ROUTES_UPDATE = 'routes.update';
    public const ROUTES_ASSIGN = 'routes.assign';

    // Zonas
    public const ZONES_VIEW = 'zones.view';
    public const ZONES_CREATE = 'zones.create';
    public const ZONES_UPDATE = 'zones.update';
    public const ZONES_DELETE = 'zones.delete';

    // Manifiestos
    public const MANIFESTS_VIEW = 'manifests.view';
    public const MANIFESTS_CREATE = 'manifests.create';
    public const MANIFESTS_UPDATE = 'manifests.update';
    public const MANIFESTS_DOWNLOAD = 'manifests.download';
    public const MANIFESTS_PRINT = 'manifests.print';

    // Captura de residuos
    public const WASTE_CAPTURE_VIEW = 'waste_capture.view';
    public const WASTE_CAPTURE_CREATE = 'waste_capture.create';
    public const WASTE_CAPTURE_UPDATE = 'waste_capture.update';
    public const WASTE_CAPTURE_UPLOAD_PHOTOS = 'waste_capture.upload_photos';

    // Jornadas de chofer
    public const DRIVER_SHIFTS_VIEW = 'driver_shifts.view';
    public const DRIVER_SHIFTS_START = 'driver_shifts.start';
    public const DRIVER_SHIFTS_FINISH = 'driver_shifts.finish';

    // Procesos ambientales
    public const ENVIRONMENTAL_PROCESSES_VIEW = 'environmental_processes.view';
    public const ENVIRONMENTAL_PROCESSES_CREATE = 'environmental_processes.create';
    public const ENVIRONMENTAL_PROCESSES_UPDATE = 'environmental_processes.update';
    public const ENVIRONMENTAL_PROCESSES_COMPLETE = 'environmental_processes.complete';
    public const ENVIRONMENTAL_PROCESSES_REPROCESS = 'environmental_processes.reprocess';

    // Bachadas
    public const BATCHES_VIEW = 'batches.view';
    public const BATCHES_CREATE = 'batches.create';
    public const BATCHES_UPDATE = 'batches.update';

    // Certificados
    public const CERTIFICATES_VIEW = 'certificates.view';
    public const CERTIFICATES_CREATE = 'certificates.create';
    public const CERTIFICATES_DOWNLOAD = 'certificates.download';
    public const CERTIFICATES_PRINT = 'certificates.print';

    // Bitácoras
    public const LOGBOOKS_VIEW = 'logbooks.view';
    public const LOGBOOKS_CREATE = 'logbooks.create';
    public const LOGBOOKS_DOWNLOAD = 'logbooks.download';

    // Facturación
    public const INVOICES_VIEW = 'invoices.view';
    public const INVOICES_CREATE = 'invoices.create';
    public const INVOICES_CANCEL = 'invoices.cancel';
    public const INVOICES_DOWNLOAD_PDF = 'invoices.download_pdf';
    public const INVOICES_DOWNLOAD_XML = 'invoices.download_xml';

    // Pagos
    public const PAYMENTS_VIEW = 'payments.view';
    public const PAYMENTS_CREATE = 'payments.create';
    public const PAYMENTS_UPDATE = 'payments.update';
    public const PAYMENTS_DELETE = 'payments.delete';

    // Reportes
    public const REPORTS_VIEW = 'reports.view';
    public const REPORTS_EXPORT = 'reports.export';

    // Documentos de clientes
    public const CUSTOMER_DOCUMENTS_VIEW = 'customer_documents.view';
    public const CUSTOMER_DOCUMENTS_DOWNLOAD = 'customer_documents.download';

    // Usuarios
    public const USERS_VIEW = 'users.view';
    public const USERS_CREATE = 'users.create';
    public const USERS_UPDATE = 'users.update';
    public const USERS_DELETE = 'users.delete';
    public const USERS_SWITCH_STATUS = 'users.switch_status';

    // Roles
    public const ROLES_VIEW = 'roles.view';
    public const ROLES_CREATE = 'roles.create';
    public const ROLES_UPDATE = 'roles.update';
    public const ROLES_DELETE = 'roles.delete';
    public const ROLES_ASSIGN_PERMISSIONS = 'roles.assign_permissions';

    // Catálogos
    public const CATALOGS_VIEW = 'catalogs.view';
    public const CATALOGS_CREATE = 'catalogs.create';
    public const CATALOGS_UPDATE = 'catalogs.update';
    public const CATALOGS_DELETE = 'catalogs.delete';

    // Configuración
    public const SETTINGS_VIEW = 'settings.view';
    public const SETTINGS_UPDATE = 'settings.update';

    // Perfil
    public const PROFILE_VIEW = 'profile.view';
    public const PROFILE_UPDATE = 'profile.update';
}