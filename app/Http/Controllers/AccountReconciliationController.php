<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkTransactionUpdateRequest;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountReconciliationController extends Controller
{
    public function index(Account $account, Request $request)
    {
        $transactions = Transaction::query()
            ->where('account_id', $account->id)
            ->with(['receivable.order', 'receivable.customer', 'payable.purchase', 'payable.supplier', 'createdBy'])
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('reconciled') && $request->reconciled !== 'all', function ($query) use ($request) {
                $query->where('reconciled', $request->reconciled === 'yes');
            })
            ->latest('transaction_date')
            ->paginate(20)
            ->withQueryString();

        return inertia('AccountReconciliation/Index', [
            'account' => $account,
            'transactions' => $transactions,
            'filters' => $request->only(['start_date', 'end_date', 'type', 'reconciled']),
        ]);
    }

    public function update(Transaction $transaction, Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'reconciled' => 'required|boolean',
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transação atualizada com sucesso!');
    }

    public function bulkUpdate(BulkTransactionUpdateRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $updateData = [];

            // Somente inclui os campos que foram preenchidos
            if (!empty($data['transaction_date'])) {
                $updateData['transaction_date'] = $data['transaction_date'];
            }

            if (isset($data['reconciled'])) {
                $updateData['reconciled'] = $data['reconciled'];
            }

            // Se não há dados para atualizar, retorna erro
            if (empty($updateData)) {
                return back()->withErrors(['error' => 'Nenhum dado fornecido para atualização.']);
            }

            // Atualiza as transações
            foreach ($data['transaction_ids'] as $id) {
                Transaction::where('id', $id)->update($updateData);
            }

            $actionMessage = isset($updateData['reconciled'])
                ? ($updateData['reconciled'] ? 'conciliadas' : 'desconciliadas')
                : 'atualizadas';

            return back()->with('success', count($data['transaction_ids']) . ' transações ' . $actionMessage . ' com sucesso!');
        });
    }

    public function selectAccount()
    {
        $accounts = Account::where('active', true)
            ->withCount(['transactions' => function ($query) {
                $query->where('reconciled', false);
            }])
            ->get();

        return inertia('AccountReconciliation/SelectAccount', [
            'accounts' => $accounts,
        ]);
    }
}
