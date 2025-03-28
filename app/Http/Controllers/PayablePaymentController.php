<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayablePaymentRequest;
use App\Models\Payable;
use App\Models\PayablePayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayablePaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = PayablePayment::query()
            ->with(['payable.supplier', 'paymentMethod', 'account', 'transaction'])
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
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->whereHas('payable', function ($subQuery) use ($request) {
                    $subQuery->where('supplier_id', $request->supplier_id);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return inertia('PayablesPayments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['start_date', 'end_date', 'supplier_id']),
        ]);
    }

    public function create(Request $request)
    {
        $ids = explode(',', $request->ids);

        if (empty(array_filter($ids))) {
            return to_route('payables.index')->with('error', 'Nenhum pagável selecionado.');
        }

        $payables = Payable::whereIn('id', $ids)
            ->with(['supplier', 'paymentMethod'])
            ->get();

        if ($payables->isEmpty()) {
            return to_route('payables.index')->with('error', 'Nenhum pagável encontrado.');
        }

        $supplierId = $payables->first()->supplier_id;

        if ($payables->some(fn ($r) => $r->supplier_id !== $supplierId)) {
            return to_route('payables.index')->with('error', 'Só é possível liquidar pagáveis do mesmo fornecedor.');
        }

        if ($payables->some(fn ($r) => $r->status === 'paid')) {
            return to_route('payables.index')->with('error', 'Não é possível liquidar pagáveis já pagos.');
        }

        return inertia('PayablesPayments/Create', [
            'payables' => $payables,
        ]);
    }

    public function store(PayablePaymentRequest $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = Transaction::create([
                'account_id' => $request->account_id,
                'type' => 'expense',
                'amount' => $request->total_paid_amount,
                'transaction_date' => $request->payment_date,
                'description' => 'Pagamento de pagáveis',
                'reconciled' => false,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->payments as $payment) {
                $payable = Payable::findOrFail($payment['payable_id']);

                $payablePayment = PayablePayment::create([
                    'payable_id' => $payment['payable_id'],
                    'payment_method_id' => $request->payment_method_id,
                    'account_id' => $request->account_id,
                    'transaction_id' => $transaction->id,
                    'payment_date' => $request->payment_date,
                    'total_amount' => $payable->total_amount,
                    'paid_amount' => $payment['paid_amount'],
                    'fees' => $payment['fees'] ?? 0,
                    'discount' => $payment['discount'] ?? 0,
                    'remaining_amount' => $payable->total_amount - $payment['paid_amount'],
                    'notes' => $request->notes ?? null,
                    'created_by' => Auth::id(),
                ]);

                $newPaidAmount = $payable->paid_amount + $payment['paid_amount'];
                $newRemainingAmount = $payable->total_amount - $newPaidAmount;
                $status = $newRemainingAmount <= 0 ? 'paid' : 'partial';

                $payable->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'status' => $status,
                ]);

                $payablePayment->account->update([
                    'current_balance' => $payablePayment->account->current_balance - $payablePayment->paid_amount,
                ]);
            }
        });

        return to_route('payables.payments.index')->with('success', 'Baixa registrado com sucesso!');
    }

    public function show(PayablePayment $payment)
    {
        return inertia('PayablesPayments/Show', [
            'payment' => $payment->load(['payable.supplier', 'paymentMethod', 'account', 'transaction']),
        ]);
    }

    public function destroy(PayablePayment $payment)
    {
        return DB::transaction(function () use ($payment) {
            $payable = $payment->payable;

            if (! $payable) {
                return to_route('payables.payments.index')->with('error', 'Pagável associado não encontrado.');
            }

            $newPaidAmount = $payable->paid_amount - $payment->paid_amount;
            $newRemainingAmount = $payable->total_amount - $newPaidAmount;

            $newStatus = 'pending';

            if ($newPaidAmount > 0) {
                $newStatus = 'partial';
            } elseif ($newPaidAmount >= $payable->total_amount) {
                $newStatus = 'paid';
            }

            $payable->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => $newRemainingAmount,
                'status' => $newStatus,
            ]);

            $payment->delete();

            if ($payment->transaction) {
                $payment->transaction->delete();
            }

            return to_route('payables.payments.index')->with('success', 'Baixa excluída e saldo do pagável restaurado com sucesso!');
        });
    }
}
