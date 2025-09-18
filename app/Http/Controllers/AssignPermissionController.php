<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPermissionController extends Controller
{
    public function index()
    {
        return Inertia::render('roles/Index', [
            'roles' => Role::all(),
        ]);
    }

    public function editRolePermissions(Role $role)
    {
        $permissions = Permission::all();
        $raw_role_permissions = $role->permissions()->get()->toArray();
        $role_permissions = [];
        for ($i = 0; $i < count($raw_role_permissions); $i++) {
            array_push($role_permissions, $raw_role_permissions[$i]['name']);
        }
        return Inertia::render('AssignPermission/EditRolePermissions', [
            'role' => $role,
            'permissions' => $permissions,
            'role_permissions' => $role_permissions,
        ]);
    }

    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'update_role_permissions' => 'required|array',
        ]);

        try {
            $role = Role::findById($request->role_id);

            if ($role) {
                $permission = Permission::whereIn('name', $request->update_role_permissions)->get();

                if ($permission->isNotEmpty()) {
                    $role->syncPermissions($permission);
                    return redirect()->route('assign_permissions.index')->with('success', 'Permissions updated successfully.');
                } else {
                    return redirect()->back()->with('error', 'No valid permissions found to assign.');
                }
            } else {
                return redirect()->back()->with('error', 'Role not found.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Role not found.');
        }
    }
}
