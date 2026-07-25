<?php

namespace App\Features\Clients\Application;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Shared\Query\BuilderFilter;
use App\Features\Shared\Query\QueryModifierCategory;
use App\Models\Client;
use Illuminate\Support\Facades\Gate;

final readonly class ListClients
{
    public function __construct(
        private BuilderFilter $builderFilter,
    ) {}

    /**
     * @param  array<int, mixed>  $modifiers
     * @return array{
     *     draw: int,
     *     recordsTotal: int,
     *     recordsFiltered: int,
     *     data: list<array<string, mixed>>
     * }
     */
    public function __invoke(
        array $modifiers = [],
        int $draw = 0,
    ): array {
        $recordsTotal = Client::query()->count();

        $filteredQuery = ($this->builderFilter)(
            builder: Client::query()->select([
                'id',
                'name',
                'parentarl_surname',
                'email',
                'phone',
                'company',
                'created_at',
            ]),
            modifiers: $modifiers,
            category: QueryModifierCategory::FILTER,
        );

        $recordsFiltered = (clone $filteredQuery)->count();

        $dataQuery = ($this->builderFilter)(
            builder: $filteredQuery,
            modifiers: $modifiers,
            category: QueryModifierCategory::OPTION,
        );

        $clients = $dataQuery->get();

        $canUpdate = Gate::allows(PermissionTypes::CLIENTS_UPDATE);
        $canDelete = Gate::allows(PermissionTypes::CLIENTS_DELETE);

        $data = $clients->map(
            static function (Client $client) use ($canUpdate, $canDelete): array {
                return [
                    'id' => $client->id,
                    'full_name' => trim(
                        "{$client->name} {$client->parentarl_surname}"
                    ),
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'company' => $client->company,
                    'created_at' => $client->created_at?->format('d/m/Y H:i'),
                    'actions' => self::actionsHtml(
                        client: $client,
                        canUpdate: $canUpdate,
                        canDelete: $canDelete,
                    ),
                ];
            },
        )->values()->all();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    private static function actionsHtml(
        Client $client,
        bool $canUpdate,
        bool $canDelete,
    ): string {
        if (! $canUpdate && ! $canDelete) {
            return '';
        }

        $html = '<div class="btn-list flex-nowrap">';

        if ($canUpdate) {
            $editUrl = e(route('clients.edit', $client));
            $html .= <<<HTML
                <a href="{$editUrl}" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-pencil me-1"></i>
                    Editar
                </a>
            HTML;
        }

        if ($canDelete) {
            $destroyUrl = e(route('clients.destroy', $client));
            $csrf = e(csrf_token());
            $html .= <<<HTML
                <form action="{$destroyUrl}" method="POST" onsubmit="return confirm('¿Eliminar este cliente?')">
                    <input type="hidden" name="_token" value="{$csrf}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="ti ti-trash me-1"></i>
                        Eliminar
                    </button>
                </form>
            HTML;
        }

        $html .= '</div>';

        return $html;
    }
}
