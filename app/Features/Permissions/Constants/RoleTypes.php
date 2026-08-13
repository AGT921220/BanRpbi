<?php

namespace App\Features\Permissions\Constants;

final class RoleTypes
{
    public const ADMIN = 'Admin';

    public const VENTAS = 'Ventas';

    public const LOGISTICA = 'Logística';

    public const CHOFER = 'Chofer';

    public const DIRECTOR_GENERAL = 'Director General';

    public const DIRECTOR_VENTAS = 'Director de Ventas';

    public const FACTURACION = 'Administración / Facturación';

    public const CLIENTE = 'Cliente';

    /**
     * Roles required for dual client-configuration approval.
     *
     * @var list<string>
     */
    public const APPROVAL_DIRECTOR_ROLES = [
        self::DIRECTOR_VENTAS,
        self::DIRECTOR_GENERAL,
    ];
}
