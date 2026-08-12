<?php

namespace App\Features\Permissions\Constants;

final class RoleTypes
{
    public const ADMIN = 'Admin';

    public const DIRECTOR_VENTAS = 'Director Ventas';

    public const DIRECTOR_GENERAL = 'Director General';

    public const VENTAS = 'Ventas';

    public const LOGISTICA = 'Logística';

    public const CHOFER = 'Chofer';

    public const FACTURACION = 'Facturación';

    public const CONSULTA = 'Consulta';

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
