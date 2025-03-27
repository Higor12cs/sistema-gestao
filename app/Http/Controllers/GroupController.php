<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::query()
            ->with('section')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Groups/Index', [
            'groups' => $groups,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Groups/Create');
    }

    public function store(GroupRequest $request)
    {
        Group::create($request->validated());

        return to_route('groups.index')->with('success', 'Grupo criado com sucesso!');
    }

    // public function show(Group $group)
    // {
    //     //
    // }

    public function edit(Group $group)
    {
        return inertia('Groups/Edit', [
            'group' => $group,
        ]);
    }

    public function update(GroupRequest $request, Group $group)
    {
        $group->update($request->validated());

        return to_route('groups.index')->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroy(Group $group)
    {
        // TODO: Check if the group has any related data before deleting it

        abort(403, 'Forbidden');

        $group->delete();

        return to_route('groups.index')->with('success', 'Grupo excluído com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $groups = Group::with('section')
                ->whereIn('id', $ids)
                ->get();

            return response()->json([
                'data' => $groups->map(function (Group $group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name.' | '.($group->section ? $group->section->name : 'Sem Seção'),
                        'section_id' => $group->section ? $group->section->id : null,
                    ];
                }),
            ]);
        }

        $query = $request->search ?? '';

        $groups = Group::with('section')
            ->where('name', 'ilike', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $groups->map(function (Group $group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name.' | '.($group->section ? $group->section->name : 'Sem Seção'),
                    'section_id' => $group->section ? $group->section->id : null,
                ];
            }),
        ]);
    }
}
