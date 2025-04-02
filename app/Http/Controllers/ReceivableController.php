<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivableRequest;
use App\Models\Customer;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $receivables = Receivable::query()
            ->with(['customer', 'paymentMethod', 'createdBy'])
            ->when($request->filled('start_date') || $request->filled('end_date'), function ($query) use ($request) {
                $dateField = $request->date_type === 'issue' ? 'issue_date' : 'due_date';

                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween($dateField, [
                        $request->start_date,
                        $request->end_date,
                    ]);
                } elseif ($request->filled('start_date')) {
                    $query->where($dateField, '>=', $request->start_date);
                } elseif ($request->filled('end_date')) {
                    $query->where($dateField, '<=', $request->end_date);
                }
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->where('status', '!=', 'paid')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $hasResults = ($request->filled('start_date') || $request->filled('end_date') ||
            $request->filled('customer_id') || $request->filled('status'))
            ? true : false;

        $selectedCustomer = $request->filled('customer_id')
            ? Customer::find($request->customer_id)
            : null;

        return inertia('Receivables/Index', [
            'receivables' => $receivables,
            'filters' => $request->only(['start_date', 'end_date', 'date_type', 'customer_id', 'status']),
            'hasResults' => $hasResults,
            'selectedCustomer' => $selectedCustomer,
        ]);
    }

    public function create()
    {
        return inertia('Receivables/Create');
    }

    public function store(ReceivableRequest $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->receivables as $receivableData) {
                Receivable::create([
                    'customer_id' => $receivableData['customer_id'],
                    'chart_account_id' => $receivableData['chart_account_id'],
                    'payment_method_id' => $receivableData['payment_method_id'],
                    'is_manual' => true,
                    'issue_date' => $receivableData['issue_date'],
                    'due_date' => $receivableData['due_date'],
                    'total_amount' => $receivableData['total_amount'],
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $receivableData['total_amount'],
                    'status' => 'pending',
                    'description' => $receivableData['description'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return to_route('receivables.index')->with('success', 'Recebíveis criados com sucesso!');
    }

    public function edit(Receivable $receivable)
    {
        if ($receivable->status === 'paid') {
            return to_route('receivables.index')->with('error', 'Não é possível editar um recebível já pago.');
        }

        return inertia('Receivables/Edit', [
            'receivable' => $receivable->load(['customer', 'paymentMethod']),
        ]);
    }

    public function update(ReceivableRequest $request, Receivable $receivable)
    {
        if ($receivable->status === 'paid') {
            return to_route('receivables.index')->with('error', 'Não é possível editar um recebível já pago.');
        }

        $receivable->update([
            'due_date' => $request->due_date,
        ]);

        return to_route('receivables.index')->with('success', 'Recebível atualizado com sucesso!');
    }

    public function destroy(Request $request)
    {
        $ids = explode(',', $request->ids);

        $hasOrderAssociated = Receivable::whereIn('id', $ids)
            ->whereNotNull('order_id')
            ->exists();

        if ($hasOrderAssociated) {
            abort(403, 'Não é possível excluir recebíveis associados a pedidos.');
        }

        Receivable::whereIn('id', $ids)->delete();

        return to_route('receivables.index')->with('success', 'Recebíveis excluídos com sucesso!');
    }
}
