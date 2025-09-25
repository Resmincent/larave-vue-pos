<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index(Request $request)
    {

        $query = $request->string('query');
        $users = User::with('roles')->when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'users/Index',
            [
                'users' => $users,
                'filters' => ['query' => $query]
            ]
        );
    }

    public function create()
    {
        return Inertia::render('users/Create', [
            'roles' => Role::all(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'is_admin' => 'sometimes|boolean',
        ]);
        // Hash password secara manual
        $data['password'] = Hash::make('pos123');

        // Buat user
        $user = User::create($data);

        // Assign role
        if ($request->filled('role')) {
            $user->assignRole($request->role);
        }

        // Generate custom id
        $customId = $user->generateCustomId();
        $user->custom_id = $customId;
        $user->save();
        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit(User $user)
    {
        return Inertia::render('users/Edit', [
            'user' => $user,
            'roles' => Role::all(),
            'users' => User::where('id', '!=', $user->id)->orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'is_admin' => 'sometimes|boolean',
        ]);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        } else {
            unset($data['password']);
        }
        $user->update($data);
        $user->syncRoles($request->role);
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->roles()->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
