<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransferRequest;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = AccountTransfer::query()
            ->with(['sourceAccount', 'destinationAccount', 'createdBy'])
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($query) use ($request) {
                $query->whereBetween('transfer_date', [$request->start_date, $request->end_date]);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return inertia('AccountTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function create()
    {
        $accounts = Account::where('active', true)->get(['id', 'name', 'current_balance']);

        return inertia('AccountTransfers/Create', [
            'accounts' => $accounts,
        ]);
    }

    public function store(AccountTransferRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $sourceAccount = Account::findOrFail($request->source_account_id);
            $destinationAccount = Account::findOrFail($request->destination_account_id);

            // Verifica se a conta de origem tem saldo suficiente
            if ($sourceAccount->current_balance < $request->amount) {
                return back()->withErrors(['amount' => 'Saldo insuficiente na conta de origem.']);
            }

            // Cria a transação de débito na conta de origem
            $debitTransaction = Transaction::create([
                'account_id' => $sourceAccount->id,
                'type' => 'expense',
                'amount' => $request->amount,
                'transaction_date' => $request->transfer_date,
                'description' => "Transferência para {$destinationAccount->name}" . ($request->notes ? " - {$request->notes}" : ""),
                'reconciled' => false,
                'created_by' => Auth::id(),
            ]);

            // Cria a transação de crédito na conta de destino
            $creditTransaction = Transaction::create([
                'account_id' => $destinationAccount->id,
                'type' => 'income',
                'amount' => $request->amount,
                'transaction_date' => $request->transfer_date,
                'description' => "Transferência de {$sourceAccount->name}" . ($request->notes ? " - {$request->notes}" : ""),
                'reconciled' => false,
                'created_by' => Auth::id(),
            ]);

            // Cria o registro de transferência
            $transfer = AccountTransfer::create([
                'source_account_id' => $sourceAccount->id,
                'destination_account_id' => $destinationAccount->id,
                'amount' => $request->amount,
                'transfer_date' => $request->transfer_date,
                'debit_transaction_id' => $debitTransaction->id,
                'credit_transaction_id' => $creditTransaction->id,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Atualiza os saldos das contas
            $sourceAccount->update([
                'current_balance' => $sourceAccount->current_balance - $request->amount,
            ]);

            $destinationAccount->update([
                'current_balance' => $destinationAccount->current_balance + $request->amount,
            ]);

            return to_route('account-transfers.index')->with('success', 'Transferência realizada com sucesso.');
        });
    }

    public function show(AccountTransfer $accountTransfer)
    {
        $accountTransfer->load([
            'sourceAccount',
            'destinationAccount',
            'debitTransaction',
            'creditTransaction',
            'createdBy'
        ]);

        return inertia('AccountTransfers/Show', [
            'transfer' => $accountTransfer,
        ]);
    }

    public function destroy(AccountTransfer $accountTransfer)
    {
        // Verifica se a transferência pode ser excluída
        if ($accountTransfer->debitTransaction->reconciled || $accountTransfer->creditTransaction->reconciled) {
            return back()->withErrors(['delete' => 'Não é possível excluir uma transferência com transações já conciliadas.']);
        }

        return DB::transaction(function () use ($accountTransfer) {
            $sourceAccount = $accountTransfer->sourceAccount;
            $destinationAccount = $accountTransfer->destinationAccount;

            // Reverte as alterações de saldo
            $sourceAccount->update([
                'current_balance' => $sourceAccount->current_balance + $accountTransfer->amount,
            ]);

            $destinationAccount->update([
                'current_balance' => $destinationAccount->current_balance - $accountTransfer->amount,
            ]);

            // Exclui as transações e a transferência
            if ($accountTransfer->debitTransaction) {
                $accountTransfer->debitTransaction->delete();
            }

            if ($accountTransfer->creditTransaction) {
                $accountTransfer->creditTransaction->delete();
            }

            $accountTransfer->delete();

            return to_route('account-transfers.index')->with('success', 'Transferência excluída com sucesso.');
        });
    }
}
