<?php

namespace App\Http\Controllers;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\PermissionHandler;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly PermissionHandler $permissionHandler
    ) {}

    public function index(): View
    {
        $this->authorize(PermissionTypes::ROLES_VIEW);

        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return view('roles.index', [
            'roles' => $roles,
            'permissionHandler' => $this->permissionHandler,
        ]);
    }

    public function create(): View
    {
        $this->authorize(PermissionTypes::ROLES_CREATE);

        return view('roles.create', [
            'groupedPermissions' => $this->permissionHandler->getGroupedPermissions(),
            'moduleLabels' => $this->permissionHandler->getModuleLabels(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);

        if ($request->user()?->can(PermissionTypes::ROLES_ASSIGN_PERMISSIONS)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role): View
    {
        $this->authorize(PermissionTypes::ROLES_UPDATE);

        $role->load('permissions');

        return view('roles.edit', [
            'role' => $role,
            'groupedPermissions' => $this->permissionHandler->getGroupedPermissions(),
            'moduleLabels' => $this->permissionHandler->getModuleLabels(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        $role->update(['name' => $validated['name']]);

        if ($request->user()?->can(PermissionTypes::ROLES_ASSIGN_PERMISSIONS)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize(PermissionTypes::ROLES_DELETE);

        if ($role->name === 'Super Administrador') {
            return back()->with('error', 'No se puede eliminar el rol Super Administrador.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un rol asignado a usuarios.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
