<?php

namespace App\Http\Controllers;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\PermissionHandler;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly PermissionHandler $permissionHandler
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::USERS_VIEW);

        $users = User::query()
            ->with(['roles', 'permissions'])
            ->latest()
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::USERS_CREATE);

        return view('users.create', $this->formData());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $this->syncRolesAndPermissions(
            $user,
            $validated['roles'] ?? [],
            $validated['permissions'] ?? []
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        $this->authorize(PermissionTypes::USERS_UPDATE);

        $user->load(['roles.permissions', 'permissions']);

        return view('users.edit', array_merge(
            $this->formData(),
            [
                'user' => $user,
                'selectedRoles' => $user->roles->pluck('name'),
                'directPermissions' => $user->getDirectPermissions()->pluck('name'),
                'lockedPermissions' => $user->getPermissionsViaRoles()->pluck('name'),
            ]
        ));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        $this->syncRolesAndPermissions(
            $user,
            $validated['roles'] ?? [],
            $validated['permissions'] ?? []
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize(PermissionTypes::USERS_DELETE);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return [
            'roles' => $roles,
            'rolesPermissionsMap' => $roles->mapWithKeys(
                fn (Role $role): array => [
                    $role->name => $role->permissions->pluck('name')->values()->all(),
                ]
            ),
            'groupedPermissions' => $this->permissionHandler->getGroupedPermissions(),
            'moduleLabels' => $this->permissionHandler->getModuleLabels(),
            'selectedRoles' => collect(),
            'directPermissions' => collect(),
            'lockedPermissions' => collect(),
        ];
    }

    /**
     * @param  array<int, string>  $roles
     * @param  array<int, string>  $permissions
     */
    private function syncRolesAndPermissions(User $user, array $roles, array $permissions): void
    {
        $user->syncRoles($roles);

        $permissionsFromRoles = Role::query()
            ->whereIn('name', $roles)
            ->with('permissions')
            ->get()
            ->flatMap(fn (Role $role): Collection => $role->permissions->pluck('name'))
            ->unique()
            ->all();

        $directPermissions = collect($permissions)
            ->diff($permissionsFromRoles)
            ->values()
            ->all();

        $user->syncPermissions($directPermissions);
    }
}
