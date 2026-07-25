# Modificadores de consultas Eloquent

Sistema reutilizable para modificar consultas Eloquent mediante objetos tipados, en lugar de arreglos mágicos.

Ubicación: `app/Features/Shared/Query/`

| Archivo | Responsabilidad |
| ------- | --------------- |
| `QueryModifierInterface.php` | Contrato común: `apply(Builder $builder): Builder` |
| `QueryFilter.php` | Filtros `where`, `whereIn`, `whereNotIn`, `whereBetween`, `whereNull`, `whereNotNull` |
| `QueryOptions.php` | Opciones `orderBy`, `offset`, `limit` |
| `ApplyQueryModifiers.php` | Clase invocable que aplica una lista de modificadores a un `Builder` |

## Reglas de diseño

1. `QueryFilter` y `QueryOptions` son `final readonly` con constructor privado; solo se instancian mediante sus métodos estáticos, por lo que el tipo de filtro nunca proviene de una petición HTTP.
2. Ningún modificador ejecuta la consulta: no llaman `get()`, `paginate()`, `first()` ni `count()`. La clase Application decide cómo terminar la consulta.
3. `ApplyQueryModifiers` ignora silenciosamente los elementos que no implementen `QueryModifierInterface` y aplica los modificadores en el orden recibido.
4. Normalizaciones automáticas en `QueryOptions`:
   - Dirección de orden inválida → `asc`.
   - Offset negativo → `0`.
   - Límite menor que `1` → `1`.
5. Los nombres de columnas no deben provenir directamente del request sin validación.

## Uso

```php
use App\Features\Shared\Query\QueryFilter;
use App\Features\Shared\Query\QueryOptions;

$modifiers = [];

if ($user->org_id !== null) {
    $modifiers[] = QueryFilter::where(
        field: 'org_id',
        value: $user->org_id,
    );
}

$modifiers[] = QueryFilter::whereIn(
    field: 'status',
    values: ['active', 'pending'],
);

$modifiers[] = QueryOptions::orderBy(
    field: 'created_at',
    direction: 'desc',
);

$modifiers[] = QueryOptions::offset(20);
$modifiers[] = QueryOptions::limit(10);

return ($this->fetchEventHeaders)($modifiers);
```

Forma compacta:

```php
return ($this->fetchEventHeaders)([
    QueryFilter::where('org_id', $user->org_id),
    QueryFilter::whereIn('status', ['active', 'pending']),
    QueryOptions::orderBy('created_at', 'desc'),
    QueryOptions::limit(25),
]);
```

## Integración en clases Application

```php
<?php

namespace App\Features\Events\Application;

use App\Features\Shared\Query\ApplyQueryModifiers;
use App\Models\EventHeader;
use Illuminate\Database\Eloquent\Collection;

final readonly class FetchEventHeaders
{
    public function __construct(
        private ApplyQueryModifiers $applyQueryModifiers,
    ) {}

    /**
     * @param  array<int, mixed>  $modifiers
     */
    public function __invoke(
        array $modifiers = [],
    ): Collection {
        $builder = EventHeader::query();

        $builder = ($this->applyQueryModifiers)(
            builder: $builder,
            modifiers: $modifiers,
        );

        return $builder->get();
    }
}
```

## Pruebas

Las pruebas viven en `tests/Unit/Shared/Query/QueryModifiersTest.php` y verifican cada filtro, cada opción, las normalizaciones, el orden de aplicación, la tolerancia a elementos inválidos y que ningún modificador ejecuta la consulta.

```bash
docker exec php-ban php artisan test tests/Unit/Shared/Query/QueryModifiersTest.php
```
