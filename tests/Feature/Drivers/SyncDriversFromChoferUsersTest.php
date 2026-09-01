<?php

namespace Tests\Feature\Drivers;

use App\Features\Drivers\Application\SyncDriversFromChoferUsers;
use App\Features\Permissions\Constants\RoleTypes;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SyncDriversFromChoferUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_drivers_for_chofer_users_without_one(): void
    {
        Role::findOrCreate(RoleTypes::CHOFER, 'web');

        $chofer = User::factory()->create([
            'name' => 'Pedro López Martínez',
        ]);
        $chofer->assignRole(RoleTypes::CHOFER);

        $created = app(SyncDriversFromChoferUsers::class)();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('drivers', [
            'user_id' => $chofer->id,
            'name' => 'Pedro',
            'parentarl_surname' => 'López',
            'maternal_surname' => 'Martínez',
        ]);
    }

    public function test_skips_chofer_users_that_already_have_a_driver(): void
    {
        Role::findOrCreate(RoleTypes::CHOFER, 'web');

        $chofer = User::factory()->create(['name' => 'Luis Chofer']);
        $chofer->assignRole(RoleTypes::CHOFER);

        Driver::factory()->create(['user_id' => $chofer->id]);

        $created = app(SyncDriversFromChoferUsers::class)();

        $this->assertSame(0, $created);
        $this->assertDatabaseCount('drivers', 1);
    }
}
