<?php

namespace App\Http\Controllers\Admin;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\PermissionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('users.index', array_merge(
            compact('users'),
            $this->formData()
        ));
    }

    public function create(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::USERS_CREATE);

        if ($request->wantsJson()) {
            return response()->json($this->formMeta());
        }

        return redirect()->route('users.index');
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario creado correctamente.',
                'user' => $user->load(['roles', 'permissions']),
            ], 201);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::USERS_UPDATE);

        $user->load(['roles.permissions', 'permissions']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
                'direct_permissions' => $user->getDirectPermissions()->pluck('name')->values(),
                'locked_permissions' => $user->getPermissionsViaRoles()->pluck('name')->values(),
                'update_url' => route('users.update', $user),
            ]);
        }

        return redirect()->route('users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario actualizado correctamente.',
                'user' => $user->fresh()->load(['roles', 'permissions']),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::USERS_DELETE);

        if (auth()->id() === $user->id) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'No puedes eliminar tu propio usuario.',
                ], 422);
            }

            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Usuario eliminado correctamente.',
            ]);
        }

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
     * @return array<string, mixed>
     */
    private function formMeta(): array
    {
        $data = $this->formData();

        return [
            'roles' => $data['roles']->map(fn (Role $role) => [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ])->values(),
            'roles_permissions_map' => $data['rolesPermissionsMap'],
            'grouped_permissions' => $data['groupedPermissions'],
            'module_labels' => $data['moduleLabels'],
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
