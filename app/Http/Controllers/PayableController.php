<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayableRequest;
use App\Models\Payable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayableController extends Controller
{
    public function index(Request $request)
    {
        $payables = Payable::query()
            ->with(['supplier', 'paymentMethod', 'createdBy'])
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
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('supplier_id', $request->supplier_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->where('status', '!=', 'paid')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $hasResults = ($request->filled('start_date') || $request->filled('end_date') ||
            $request->filled('supplier_id') || $request->filled('status'))
            ? true : false;

        $selectedSupplier = $request->filled('supplier_id')
            ? Supplier::find($request->supplier_id)
            : null;

        return inertia('Payables/Index', [
            'payables' => $payables,
            'filters' => $request->only(['start_date', 'end_date', 'date_type', 'supplier_id', 'status']),
            'hasResults' => $hasResults,
            'selectedSupplier' => $selectedSupplier,
        ]);
    }

    public function create()
    {
        return inertia('Payables/Create');
    }

    public function store(PayableRequest $request)
    {
        DB::transaction(function () use ($request) {
            foreach ($request->payables as $payableData) {
                Payable::create([
                    'supplier_id' => $payableData['supplier_id'],
                    'chart_account_id' => $payableData['chart_account_id'],
                    'payment_method_id' => $payableData['payment_method_id'],
                    'is_manual' => true,
                    'issue_date' => $payableData['issue_date'],
                    'due_date' => $payableData['due_date'],
                    'total_amount' => $payableData['total_amount'],
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $payableData['total_amount'],
                    'status' => 'pending',
                    'description' => $payableData['description'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return to_route('payables.index')->with('success', 'Pagáveis criados com sucesso!');
    }

    public function edit(Payable $payable)
    {
        if ($payable->status === 'paid') {
            return to_route('payables.index')->with('error', 'Não é possível editar um pagável já pago.');
        }

        return inertia('Payables/Edit', [
            'payable' => $payable->load(['supplier', 'paymentMethod']),
        ]);
    }

    public function update(PayableRequest $request, Payable $payable)
    {
        if ($payable->status === 'paid') {
            return to_route('payables.index')->with('error', 'Não é possível editar um pagável já pago.');
        }

        $payable->update([
            'due_date' => $request->due_date,
        ]);

        return to_route('payables.index')->with('success', 'Pagável atualizado com sucesso!');
    }

    public function destroy(Request $request)
    {
        $ids = explode(',', $request->ids);

        $hasPurchaseAssociated = Payable::whereIn('id', $ids)
            ->whereNotNull('purchase_id')
            ->exists();

        if ($hasPurchaseAssociated) {
            abort(403, 'Não é possível excluir pagáveis associados a compras.');
        }

        Payable::whereIn('id', $ids)->delete();

        return to_route('payables.index')->with('success', 'Pagáveis excluídos com sucesso!');
    }
}
