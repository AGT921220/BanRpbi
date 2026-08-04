# Modificadores de consultas Eloquent

Sistema reutilizable para modificar consultas Eloquent mediante objetos tipados, en lugar de arreglos mágicos.

Ubicación: `app/Features/Shared/Query/`

| Archivo | Responsabilidad |
| ------- | --------------- |
| `QueryModifierInterface.php` | Contrato común: `apply()` y `category()` |
| `QueryModifierCategory.php` | Enum `FILTER` / `OPTION` |
| `QueryFilter.php` | Filtros `where`, `whereIn`, `whereNotIn`, `whereBetween`, `whereNull`, `whereNotNull`, `whereAnyLike` |
| `QueryOptions.php` | Opciones `orderBy`, `offset`, `limit` |
| `BuilderFilter.php` | Clase invocable que aplica modificadores a un `Builder`, opcionalmente por categoría |

## Reglas de diseño

1. `QueryFilter` y `QueryOptions` son `final readonly` con constructor privado; solo se instancian mediante sus métodos estáticos.
2. Ningún modificador ejecuta la consulta. `BuilderFilter` solo modifica el `Builder`.
3. `BuilderFilter` ignora silenciosamente elementos que no implementen `QueryModifierInterface`.
4. Con `category: QueryModifierCategory::FILTER` solo aplica filtros; con `OPTION` solo opciones; con `null` aplica todos.
5. Normalizaciones en `QueryOptions`: dirección inválida → `asc`; offset negativo → `0`; límite &lt; 1 → `1`.

## Uso con proyecciones Headers (Clients)

El controlador JSON (`ClientHeaderController`) construye los modificadores y `SearchClientHeaders` aplica primero filtros (para `filtered`) y después opciones (para la página). El `$draw` de DataTables nunca entra al Caso de Uso:

```php
$modifiers = [];

$modifiers[] = QueryFilter::whereAnyLike(
    fields: ['name', 'parentarl_surname', 'email', 'phone', 'company'],
    value: $search,
);

$modifiers[] = QueryOptions::orderBy(field: 'created_at', direction: 'desc');
$modifiers[] = QueryOptions::offset(20);
$modifiers[] = QueryOptions::limit(10);

$result = ($this->searchClientHeaders)(
    modifiers: $modifiers,
    offset: 20,
    limit: 10,
);
```

## Pruebas

```bash
docker exec php-ban php artisan test tests/Unit/Shared/Query/QueryModifiersTest.php tests/Feature/Clients/SearchClientHeadersTest.php tests/Feature/Clients/ClientHeaderControllerTest.php
```
