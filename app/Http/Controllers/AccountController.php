<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Accounts/Index', [
            'accounts' => $accounts,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Accounts/Create');
    }

    public function store(AccountRequest $request)
    {
        Account::create($request->validated());

        return to_route('accounts.index')->with('success', 'Conta criada com sucesso!');
    }

    // public function show(Account $account)
    // {
    //     //
    // }

    public function edit(Account $account)
    {
        return inertia('Accounts/Edit', [
            'account' => $account,
        ]);
    }

    public function update(AccountRequest $request, Account $account)
    {
        $account->update($request->validated());

        return to_route('accounts.index')->with('success', 'Conta atualizada com sucesso!');
    }

    public function destroy(Account $account)
    {
        // TODO: Check if the account has any related data before deleting it

        abort(403, 'Forbidden');

        $account->delete();

        return to_route('accounts.index')->with('success', 'Conta excluída com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $sections = Account::whereIn('id', $ids)
                ->get(['id', 'name']);

            return response()->json([
                'data' => $sections,
            ]);
        }

        $query = $request->search ?? '';

        $sections = Account::query()
            ->where('name', 'ilike', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $sections,
        ]);
    }
}
