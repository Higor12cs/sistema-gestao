<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Roles/Index', [
            'roles' => $roles,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return inertia('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('roles', 'name')->where('tenant_id', Auth::user()->tenant_id),
            ],
            'permissions' => 'array',
        ], [
            'required' => 'O campo é obrigatório.',
            'unique' => 'O valor informado já está em uso.',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissionNames = Permission::whereIn('id', $request->permissions)
                ->pluck('name')
                ->toArray();

            $role->syncPermissions($permissionNames);
        }

        return to_route('roles.index')->with('success', 'Papel criado com sucesso!');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return inertia('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('roles', 'name')->where('tenant_id', Auth::user()->tenant_id)->ignore($role->id),
            ],
            'permissions' => 'array',
        ], [
            'required' => 'O campo é obrigatório.',
            'unique' => 'O valor informado já está em uso.',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permissionNames = Permission::whereIn('id', $request->permissions)
                ->pluck('name')
                ->toArray();
            $role->syncPermissions($permissionNames);
        } else {
            $role->permissions()->detach();
        }

        return to_route('roles.index')->with('success', 'Papel atualizado com sucesso!');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Administrador') {
            return back()->withErrors(['error' => 'O papel Administrador não pode ser excluído.']);
        }

        $role->delete();

        return to_route('roles.index')->with('success', 'Papel excluído com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $roles = Role::whereIn('id', $ids)
                ->where('tenant_id', Auth::user()->tenant_id)
                ->get(['id', 'name']);

            return response()->json([
                'data' => $roles,
            ]);
        }

        $query = $request->search ?? '';

        $roles = Role::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $roles,
        ]);
    }
}
