<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfWeek()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfWeek()->endOfDay();

        return Inertia::render('CashFlow/Index', [
            'metrics' => $this->getMetrics($startDate, $endDate),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    public function getDailyDetails(Request $request)
    {
        $date = Carbon::parse($request->input('date'))->format('Y-m-d');

        $transactions = Transaction::where('tenant_id', Auth::user()->tenant_id)
            ->whereDate('transaction_date', $date)
            ->with(['account', 'receivable.customer', 'payable.supplier'])
            ->get();

        $receivables = Receivable::where('tenant_id', Auth::user()->tenant_id)
            ->whereDate('due_date', $date)
            ->where('status', '!=', 'paid')
            ->with('customer', 'paymentMethod')
            ->get();

        $payables = Payable::where('tenant_id', Auth::user()->tenant_id)
            ->whereDate('due_date', $date)
            ->where('status', '!=', 'paid')
            ->with('supplier', 'paymentMethod')
            ->get();

        return response()->json([
            'transactions' => $transactions,
            'receivables' => $receivables,
            'payables' => $payables,
            'date' => $date,
        ]);
    }

    private function getMetrics($startDate, $endDate)
    {
        return [
            'cashFlowData' => $this->getCashFlowData($startDate, $endDate),
            'summaryData' => $this->getSummaryData($startDate, $endDate),
            'accountBalances' => $this->getAccountBalances(),
        ];
    }

    private function calculateRealBalanceUntilDate($endDate)
    {
        $accountsBalance = DB::table('accounts')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('active', true)
            ->sum('current_balance');

        $transactionsAfterEndDate = Transaction::where('tenant_id', Auth::user()->tenant_id)
            ->where('transaction_date', '>', $endDate)
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as balance")
            ->first();

        $amountAfterEndDate = $transactionsAfterEndDate ? $transactionsAfterEndDate->balance : 0;

        return $accountsBalance - $amountAfterEndDate;
    }

    private function getCashFlowData($startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate) + 1;

        $currentBalance = $this->calculateRealBalanceUntilDate($startDate);

        $actualTransactions = Transaction::select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
        )
            ->where('tenant_id', Auth::user()->tenant_id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $expectedReceivables = Receivable::select(
            DB::raw('DATE(due_date) as date'),
            DB::raw('SUM(remaining_amount) as amount')
        )
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $expectedPayables = Payable::select(
            DB::raw('DATE(due_date) as date'),
            DB::raw('SUM(remaining_amount) as amount')
        )
            ->where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates = [];
        $expected_incomes = [];
        $expected_expenses = [];

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dateString = $currentDate->format('Y-m-d');
            $dates[] = $dateString;

            $expected_incomes[$dateString] = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->income : 0;
            $expected_expenses[$dateString] = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->expense : 0;

            if (isset($expectedReceivables[$dateString])) {
                $expected_incomes[$dateString] += $expectedReceivables[$dateString]->amount;
            }

            if (isset($expectedPayables[$dateString])) {
                $expected_expenses[$dateString] += $expectedPayables[$dateString]->amount;
            }
        }

        $dailyData = [];
        $runningBalance = $currentBalance;
        $cumulativeIncome = 0;
        $cumulativeExpense = 0;

        foreach ($dates as $dateString) {
            $cumulativeIncome += $expected_incomes[$dateString];
            $cumulativeExpense += $expected_expenses[$dateString];

            $dailyIncome = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->income : 0;
            $dailyExpense = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->expense : 0;
            $runningBalance += ($dailyIncome - $dailyExpense);

            $projectedBalance = $currentBalance + $cumulativeIncome - $cumulativeExpense;

            $dailyData[] = [
                'date' => $dateString,
                'actual_income' => $dailyIncome,
                'actual_expense' => $dailyExpense,
                'expected_income' => isset($expectedReceivables[$dateString]) ? $expectedReceivables[$dateString]->amount : 0,
                'expected_expense' => isset($expectedPayables[$dateString]) ? $expectedPayables[$dateString]->amount : 0,
                'balance' => $runningBalance,
                'projected_balance' => $projectedBalance,
            ];
        }

        return $dailyData;
    }

    private function getSummaryData($startDate, $endDate)
    {
        $currentBalance = $this->calculateRealBalanceUntilDate($endDate);

        $totalReceivables = Receivable::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('remaining_amount');

        $totalPayables = Payable::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('remaining_amount');

        $overdueReceivables = Receivable::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->where('due_date', '<', Carbon::now())
            ->sum('remaining_amount');

        $overduePayables = Payable::where('tenant_id', Auth::user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('remaining_amount', '>', 0)
            ->where('due_date', '<', Carbon::now())
            ->sum('remaining_amount');

        return [
            'currentBalance' => $currentBalance,
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'expectedBalance' => $currentBalance + $totalReceivables - $totalPayables,
            'overdueReceivables' => $overdueReceivables,
            'overduePayables' => $overduePayables,
        ];
    }

    private function getAccountBalances()
    {
        return Account::where('tenant_id', Auth::user()->tenant_id)
            ->where('active', true)
            ->select('id', 'name', 'current_balance')
            ->get();
    }
}
