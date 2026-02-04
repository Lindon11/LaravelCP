<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * List all roles
     */
    public function indexRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * List all permissions
     */
    public function indexPermissions()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0] ?? 'other';
        });

        return response()->json($permissions);
    }

    /**
     * Create role
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ], 201);
    }

    /**
     * Update role
     */
    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->fresh()->load('permissions')
        ]);
    }

    /**
     * Delete role
     */
    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(Request $request, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user = \App\Core\Models\User::findOrFail($userId);
        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
            'user' => $user->fresh()->load('roles')
        ]);
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(Request $request, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user = \App\Core\Models\User::findOrFail($userId);
        $user->removeRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'Role removed successfully',
            'user' => $user->fresh()->load('roles')
        ]);
    }
}
