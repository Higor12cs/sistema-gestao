<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivablePaymentRequest;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivablePaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = ReceivablePayment::query()
            ->with(['receivable.customer', 'paymentMethod', 'account', 'transaction'])
            ->when($request->filled('start_date') || $request->filled('end_date'), function ($query) use ($request) {
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween('payment_date', [
                        $request->start_date,
                        $request->end_date,
                    ]);
                } elseif ($request->filled('start_date')) {
                    $query->where('payment_date', '>=', $request->start_date);
                } elseif ($request->filled('end_date')) {
                    $query->where('payment_date', '<=', $request->end_date);
                }
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->whereHas('receivable', function ($subQuery) use ($request) {
                    $subQuery->where('customer_id', $request->customer_id);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return inertia('ReceivablesPayments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['start_date', 'end_date', 'customer_id']),
        ]);
    }

    public function create(Request $request)
    {
        $ids = explode(',', $request->ids);

        if (empty(array_filter($ids))) {
            return to_route('receivables.index')->with('error', 'Nenhum recebível selecionado.');
        }

        $receivables = Receivable::whereIn('id', $ids)
            ->with(['customer', 'paymentMethod'])
            ->get();

        if ($receivables->isEmpty()) {
            return to_route('receivables.index')->with('error', 'Nenhum recebível encontrado.');
        }

        $customerId = $receivables->first()->customer_id;

        if ($receivables->some(fn($r) => $r->customer_id !== $customerId)) {
            return to_route('receivables.index')->with('error', 'Só é possível liquidar recebíveis do mesmo cliente.');
        }

        if ($receivables->some(fn($r) => $r->status === 'paid')) {
            return to_route('receivables.index')->with('error', 'Não é possível liquidar recebíveis já pagos.');
        }

        return inertia('ReceivablesPayments/Create', [
            'receivables' => $receivables,
        ]);
    }

    public function store(ReceivablePaymentRequest $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = Transaction::create([
                'account_id' => $request->account_id,
                'type' => 'income',
                'amount' => $request->total_paid_amount,
                'transaction_date' => $request->payment_date,
                'description' => 'Pagamento de recebíveis',
                'reconciled' => false,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->payments as $payment) {
                $receivable = Receivable::findOrFail($payment['receivable_id']);

                $receivablePayment = ReceivablePayment::create([
                    'receivable_id' => $payment['receivable_id'],
                    'payment_method_id' => $request->payment_method_id,
                    'account_id' => $request->account_id,
                    'transaction_id' => $transaction->id,
                    'payment_date' => $request->payment_date,
                    'total_amount' => $receivable->total_amount,
                    'paid_amount' => $payment['paid_amount'],
                    'fees' => $payment['fees'] ?? 0,
                    'discount' => $payment['discount'] ?? 0,
                    'remaining_amount' => $receivable->total_amount - $payment['paid_amount'],
                    'notes' => $request->notes ?? null,
                    'created_by' => Auth::id(),
                ]);

                $newPaidAmount = $receivable->paid_amount + $payment['paid_amount'];
                $newRemainingAmount = $receivable->total_amount - $newPaidAmount;
                $status = $newRemainingAmount <= 0 ? 'paid' : 'partial';

                $receivable->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'status' => $status,
                ]);

                $receivablePayment->account->update([
                    'current_balance' => $receivablePayment->account->current_balance + $receivablePayment->paid_amount,
                ]);
            }
        });

        return to_route('receivables.payments.index')->with('success', 'Liquidação registrada com sucesso!');
    }

    public function show(ReceivablePayment $payment)
    {
        return inertia('ReceivablesPayments/Show', [
            'payment' => $payment->load(['receivable.customer', 'paymentMethod', 'account', 'transaction']),
        ]);
    }

    public function destroy(ReceivablePayment $payment)
    {
        return DB::transaction(function () use ($payment) {
            $receivable = $payment->receivable;

            if (! $receivable) {
                return to_route('receivables.payments.index')->with('error', 'Recebível associado não encontrado.');
            }

            $newPaidAmount = $receivable->paid_amount - $payment->paid_amount;
            $newRemainingAmount = $receivable->total_amount - $newPaidAmount;

            $newStatus = 'pending';

            if ($newPaidAmount > 0) {
                $newStatus = 'partial';
            } elseif ($newPaidAmount >= $receivable->total_amount) {
                $newStatus = 'paid';
            }

            $receivable->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newStatus,
            ]);

            $payment->delete();

            if ($payment->transaction) {
                $payment->transaction->delete();
            }

            return to_route('receivables.payments.index')->with('success', 'Liquidação excluída e saldo do recebível restaurado com sucesso!');
        });
    }
}
