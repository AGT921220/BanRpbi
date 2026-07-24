@php
    use App\Features\Permissions\Constants\PermissionTypes;
@endphp



{{-- HEADER PRINCIPAL --}}
<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbar-menu"
            aria-controls="navbar-menu"
            aria-expanded="false"
            aria-label="Mostrar navegación"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            <a href="{{ route('dashboard') }}" aria-label="BAN RPBI">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="BAN RPBI"
                    class="navbar-brand-image"
                    style="width: 50px; height: auto;"
                >
            </a>
        </div>

        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item dropdown">
                <a
                    href="#"
                    class="nav-link d-flex lh-1 p-0 px-2"
                    data-bs-toggle="dropdown"
                    aria-label="Abrir menú de usuario"
                    aria-expanded="false"
                >
                    <span
                        class="avatar avatar-sm"
                        style="background-image: url('{{ asset('images/empty-user.png') }}')"
                    ></span>

                    <div class="d-none d-xl-block ps-2">
                        <div>{{ auth()->user()->name }}</div>

                        <div class="mt-1 small text-secondary">
                            {{ auth()->user()->roles->first()?->name ?? 'Usuario' }}
                        </div>
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <div class="dropdown-header">
                        <strong>{{ auth()->user()->name }}</strong>

                        <div class="small text-secondary">
                            {{ auth()->user()->email }}
                        </div>
                    </div>

                    <div class="dropdown-divider"></div>

                    @can(PermissionTypes::PROFILE_VIEW)
                        <a href="#" class="dropdown-item">
                            <i class="ti ti-user me-2"></i>
                            Mi perfil
                        </a>
                    @endcan

                    @can(PermissionTypes::SETTINGS_VIEW)
                        <a href="#" class="dropdown-item">
                            <i class="ti ti-settings me-2"></i>
                            Configuración
                        </a>
                    @endcan

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="dropdown-item">
                            <i class="ti ti-logout me-2"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>

{{-- MENÚ PRINCIPAL --}}
<header class="navbar-expand-md">
    <div id="navbar-menu" class="collapse navbar-collapse">
        <div class="navbar">
            <div class="container-xl">
                <div class="row flex-column flex-md-row flex-fill align-items-center">
                    <div class="col">
                        <ul class="navbar-nav">

                            {{-- DASHBOARD --}}
                            @can(PermissionTypes::DASHBOARD_VIEW)
                                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <a
                                        class="nav-link"
                                        href="{{ route('dashboard') }}"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-layout-dashboard"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Dashboard
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            {{-- VENTAS --}}
                            @canany([
                                PermissionTypes::CLIENTS_VIEW,
                                PermissionTypes::CONTRACTS_VIEW,
                                PermissionTypes::APPROVALS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-ventas"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-briefcase"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Ventas
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::CLIENTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-building me-2"></i>
                                                Clientes
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::CONTRACTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-file-description me-2"></i>
                                                Contratos
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::APPROVALS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-circle-check me-2"></i>
                                                Aprobaciones
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- LOGÍSTICA --}}
                            @canany([
                                PermissionTypes::COLLECTIONS_VIEW,
                                PermissionTypes::ROUTES_VIEW,
                                PermissionTypes::ZONES_VIEW,
                                PermissionTypes::MANIFESTS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-logistica"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-truck-delivery"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Logística
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::COLLECTIONS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-calendar-event me-2"></i>
                                                Recolecciones
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::ROUTES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-route me-2"></i>
                                                Rutas
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::ZONES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-map-2 me-2"></i>
                                                Zonas
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::MANIFESTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-file-certificate me-2"></i>
                                                Manifiestos
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- OPERACIÓN --}}
                            @canany([
                                PermissionTypes::WASTE_CAPTURE_VIEW,
                                PermissionTypes::DRIVER_SHIFTS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-operacion"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-steering-wheel"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Operación
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::WASTE_CAPTURE_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-trash me-2"></i>
                                                Captura de residuos
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::DRIVER_SHIFTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-road me-2"></i>
                                                Jornadas de chofer
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- PROCESOS AMBIENTALES --}}
                            @canany([
                                PermissionTypes::ENVIRONMENTAL_PROCESSES_VIEW,
                                PermissionTypes::BATCHES_VIEW,
                                PermissionTypes::CERTIFICATES_VIEW,
                                PermissionTypes::LOGBOOKS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-procesos"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-recycle"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Procesos ambientales
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::ENVIRONMENTAL_PROCESSES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-recycle me-2"></i>
                                                Procesos
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::BATCHES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-layers-intersect me-2"></i>
                                                Bachadas
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::CERTIFICATES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-certificate me-2"></i>
                                                Certificados
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::LOGBOOKS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-clipboard-text me-2"></i>
                                                Bitácoras
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- FACTURACIÓN --}}
                            @canany([
                                PermissionTypes::INVOICES_VIEW,
                                PermissionTypes::PAYMENTS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-facturacion"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-receipt"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Facturación
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::INVOICES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-file-invoice me-2"></i>
                                                Facturas
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::PAYMENTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-cash me-2"></i>
                                                Pagos
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- CONSULTAS --}}
                            @canany([
                                PermissionTypes::REPORTS_VIEW,
                                PermissionTypes::CUSTOMER_DOCUMENTS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-consultas"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-search"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Consultas
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::REPORTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-chart-bar me-2"></i>
                                                Reportes
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::CUSTOMER_DOCUMENTS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-folder me-2"></i>
                                                Documentos de clientes
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany

                            {{-- ADMINISTRACIÓN --}}
                            @canany([
                                PermissionTypes::USERS_VIEW,
                                PermissionTypes::ROLES_VIEW,
                                PermissionTypes::CATALOGS_VIEW,
                                PermissionTypes::SETTINGS_VIEW,
                            ])
                                <li class="nav-item dropdown">
                                    <a
                                        class="nav-link dropdown-toggle"
                                        href="#navbar-administracion"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        role="button"
                                        aria-expanded="false"
                                    >
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <i class="ti ti-settings"></i>
                                        </span>

                                        <span class="nav-link-title">
                                            Administración
                                        </span>
                                    </a>

                                    <div class="dropdown-menu">
                                        @can(PermissionTypes::USERS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-users me-2"></i>
                                                Usuarios
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::ROLES_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-shield-lock me-2"></i>
                                                Roles y permisos
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::CATALOGS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-list-details me-2"></i>
                                                Catálogos
                                            </a>
                                        @endcan

                                        @can(PermissionTypes::SETTINGS_VIEW)
                                            <a href="#" class="dropdown-item">
                                                <i class="ti ti-adjustments me-2"></i>
                                                Configuración
                                            </a>
                                        @endcan
                                    </div>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>