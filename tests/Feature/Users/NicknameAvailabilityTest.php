<?php

namespace Tests\Feature\Users;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NicknameAvailabilityTest extends TestCase
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

    public function test_login_uses_nickname_instead_of_email(): void
    {
        $user = User::factory()->create([
            'nickname' => 'vendedor1',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'nickname' => 'Vendedor1',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_check_nickname_reports_taken_and_available(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::USERS_CREATE,
            PermissionTypes::USERS_UPDATE,
        ]);

        User::factory()->create(['nickname' => 'ocupado']);

        $this->getJson(route('users.check-nickname', ['nickname' => 'ocupado']))
            ->assertOk()
            ->assertJson([
                'available' => false,
                'nickname' => 'ocupado',
            ]);

        $this->getJson(route('users.check-nickname', ['nickname' => 'libre']))
            ->assertOk()
            ->assertJson([
                'available' => true,
                'nickname' => 'libre',
            ]);
    }

    public function test_check_nickname_ignores_current_user_on_edit(): void
    {
        $actor = $this->actingAsUserWithPermissions([
            PermissionTypes::USERS_UPDATE,
        ]);

        $this->getJson(route('users.check-nickname', [
            'nickname' => $actor->nickname,
            'ignore_user_id' => $actor->id,
        ]))
            ->assertOk()
            ->assertJson([
                'available' => true,
            ]);
    }

    public function test_store_persists_unique_nickname(): void
    {
        $this->actingAsUserWithPermissions([
            PermissionTypes::USERS_CREATE,
            PermissionTypes::USERS_VIEW,
        ]);

        $response = $this->post(route('users.store'), [
            'name' => 'Nuevo Usuario',
            'nickname' => 'Nuevo.User',
            'email' => 'nuevo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'nickname' => 'nuevo.user',
        ]);
    }

    public function test_cannot_store_duplicate_nickname(): void
    {
        $this->actingAsUserWithPermissions([PermissionTypes::USERS_CREATE]);

        User::factory()->create(['nickname' => 'duplicado']);

        $response = $this->from(route('users.index'))->post(route('users.store'), [
            'name' => 'Otro',
            'nickname' => 'duplicado',
            'email' => 'otro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('nickname');
    }
}
