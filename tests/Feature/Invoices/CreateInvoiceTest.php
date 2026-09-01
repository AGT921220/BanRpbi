<?php

namespace Tests\Feature\Invoices;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  list<string>  $permissions
     */
    private function actingAsUserWithPermissions(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        $this->actingAs($user);

        return $user;
    }

    public function test_authorized_user_can_view_create_invoice_page(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::INVOICES_CREATE,
        ]);

        $response = $this->get(route('invoices.create'));

        $response->assertOk();
        $response->assertSee('Crear factura');
        $response->assertSee('Servicios por facturar');
    }

    public function test_billable_service_headers_returns_services_without_invoice(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::INVOICES_CREATE]);

        $client = Client::factory()->create();
        $billable = Service::query()->create([
            'service_date' => now()->toDateString(),
            'zone_id' => \App\Models\Zone::factory()->create()->id,
            'client_id' => $client->id,
            'contract_id' => \App\Models\Contract::factory()->create()->id,
            'status' => Service::STATUS_PENDING,
        ]);

        $response = $this->get(route('invoice-billable-service-headers.index', [
            'client_id' => $client->id,
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $billable->id);
    }
}
