<?php

namespace App\Http\Controllers;

use App\Features\Permissions\Constants\PermissionTypes;
use App\Features\Permissions\PermissionHandler;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'groupedPermissions' => $this->permissionHandler->getGroupedPermissions(),
            'moduleLabels' => $this->permissionHandler->getModuleLabels(),
        ]);
    }

    public function create(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::ROLES_CREATE);

        if ($request->wantsJson()) {
            return response()->json([
                'grouped_permissions' => $this->permissionHandler->getGroupedPermissions(),
                'module_labels' => $this->permissionHandler->getModuleLabels(),
            ]);
        }

        return redirect()->route('roles.index');
    }

    public function store(StoreRoleRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $role = Role::create(['name' => $validated['name']]);

        if ($request->user()?->can(PermissionTypes::ROLES_ASSIGN_PERMISSIONS)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rol creado correctamente.',
                'role' => $role->load('permissions'),
            ], 201);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::ROLES_UPDATE);

        $role->load('permissions');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
                'update_url' => route('roles.update', $role),
            ]);
        }

        return redirect()->route('roles.index');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $role->update(['name' => $validated['name']]);

        if ($request->user()?->can(PermissionTypes::ROLES_ASSIGN_PERMISSIONS)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rol actualizado correctamente.',
                'role' => $role->fresh()->load('permissions'),
            ]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->authorize(PermissionTypes::ROLES_DELETE);

        if ($role->name === 'Super Administrador') {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar el rol Super Administrador.',
                ], 422);
            }

            return back()->with('error', 'No se puede eliminar el rol Super Administrador.');
        }

        if ($role->users()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'No se puede eliminar un rol asignado a usuarios.',
                ], 422);
            }

            return back()->with('error', 'No se puede eliminar un rol asignado a usuarios.');
        }

        $role->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Rol eliminado correctamente.',
            ]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
