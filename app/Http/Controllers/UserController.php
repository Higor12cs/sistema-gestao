<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        if ($request->has('role_id') && $request->role_id) {
            $user->assignRole(Role::findOrFail($request->role_id));
        }

        return to_route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(int $sequential_id): Response
    {
        $user = User::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('sequential_id', $sequential_id)
            ->firstOrFail();

        $userRoleId = $user->roles->first() ? $user->roles->first()->id : null;

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'userRoleId' => $userRoleId,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($user->hasRole('Administrador') && $request->has('role_id')) {
            $newRole = Role::find($request->role_id);
            $isRemovingAdmin = ! $newRole || $newRole->name !== 'Administrador';

            if ($isRemovingAdmin) {
                $adminCount = User::role('Administrador')
                    ->where('tenant_id', Auth::user()->tenant_id)
                    ->count();

                if ($adminCount === 1) {
                    return back()->withErrors([
                        'role_id' => 'Não é possível remover o papel de Administrador deste usuário pois ele é o único administrador.',
                    ]);
                }
            }
        }

        $user->update($request->validated());

        if ($request->has('role_id')) {
            $user->syncRoles([]);

            if ($request->role_id) {
                $user->assignRole(Role::findOrFail($request->role_id));
            }
        }

        return to_route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($user->hasRole('Administrador')) {
            $adminCount = User::role('Administrador')
                ->where('tenant_id', Auth::user()->tenant_id)
                ->count();

            if ($adminCount === 1) {
                return back()->withErrors([
                    'delete' => 'Não é possível excluir o único usuário administrador.',
                ]);
            }
        }

        $user->delete();

        return to_route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }

    public function search(Request $request)
    {
        $query = $request->input('search', '');
        $ids = $request->input('ids');

        if ($ids) {
            return User::query()
                ->where('tenant_id', Auth::user()->tenant_id)
                ->whereIn('id', explode(',', $ids))
                ->take(10)
                ->get(['id', 'name']);
        }

        return User::query()
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->take(10)
            ->get(['id', 'name']);
    }
}
