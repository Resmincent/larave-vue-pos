<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $roles = Role::when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('roles/Index', [
            'roles' => $roles,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('roles/Create', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'guard_name' => 'required|string|max:50',
        ]);

        Role::create($data);
        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function edit(Role $role)
    {
        return Inertia::render(
            'Roles/Edit',
            [
                'role' => $role
            ]
        );
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'guard_name' => 'required|string|max:50',
        ]);

        $role->update($data);
        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully');
    }
}
