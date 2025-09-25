<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $permissions = Permission::when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('permissions/Index', [
            'permissions' => $permissions,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('permissions/Create', [
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:100|unique:permissions,name',
                'guard_name' => 'required|string|max:50',
            ]
        );

        Permission::create($data);
        return redirect()->route('permissions.index')->with('success', 'Permission created successfully');
    }

    public function edit(Permission $permission)
    {
        return Inertia::render(
            'permissions/Edit',
            [
                'permission' => $permission
            ]
        );
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
                'guard_name' => 'required|string|max:50',
            ]
        );
        $permission->update($data);
        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->back()->with('success', 'Permission deleted successfully');
    }
}
