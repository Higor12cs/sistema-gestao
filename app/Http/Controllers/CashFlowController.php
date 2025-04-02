<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->addDays(15)->endOfDay();

        return Inertia::render('CashFlow/Index', [
            'metrics' => $this->getMetrics($startDate, $endDate),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    public function getDailyDetails(Request $request)
    {
        $date = Carbon::parse($request->input('date'))->format('Y-m-d');

        // Get transactions for the day
        $transactions = Transaction::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('transaction_date', $date)
            ->with(['account', 'receivable.customer', 'payable.supplier'])
            ->get();

        // Get receivables due on that day
        $receivables = Receivable::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('due_date', $date)
            ->with('customer', 'paymentMethod')
            ->get();

        // Get payables due on that day
        $payables = Payable::where('tenant_id', auth()->user()->tenant_id)
            ->whereDate('due_date', $date)
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

    private function getCashFlowData($startDate, $endDate)
    {
        $days = $startDate->diffInDays($endDate) + 1;

        // Get current account balance
        $currentBalance = DB::table('accounts')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->sum('current_balance');

        // Get actual transactions in the period
        $actualTransactions = Transaction::select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
            DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
        )
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get expected receivables in the period
        $expectedReceivables = Receivable::select(
            DB::raw('DATE(due_date) as date'),
            DB::raw('SUM(remaining_amount) as amount')
        )
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Get expected payables in the period
        $expectedPayables = Payable::select(
            DB::raw('DATE(due_date) as date'),
            DB::raw('SUM(remaining_amount) as amount')
        )
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Prepare data containers for all days in the period
        $dates = [];
        $expected_incomes = [];
        $expected_expenses = [];

        // Create array with dates for the period
        for ($i = 0; $i < $days; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $dateString = $currentDate->format('Y-m-d');
            $dates[] = $dateString;

            // Initialize values with actual transactions
            $expected_incomes[$dateString] = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->income : 0;
            $expected_expenses[$dateString] = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->expense : 0;

            // Add expected receivables
            if (isset($expectedReceivables[$dateString])) {
                $expected_incomes[$dateString] += $expectedReceivables[$dateString]->amount;
            }

            // Add expected payables
            if (isset($expectedPayables[$dateString])) {
                $expected_expenses[$dateString] += $expectedPayables[$dateString]->amount;
            }
        }

        // Prepare daily data with accumulated balance
        $dailyData = [];
        $runningBalance = $currentBalance;
        $cumulativeIncome = 0;
        $cumulativeExpense = 0;

        foreach ($dates as $dateString) {
            $cumulativeIncome += $expected_incomes[$dateString];
            $cumulativeExpense += $expected_expenses[$dateString];

            // Update running balance for each day
            $dailyIncome = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->income : 0;
            $dailyExpense = isset($actualTransactions[$dateString]) ? $actualTransactions[$dateString]->expense : 0;
            $runningBalance += ($dailyIncome - $dailyExpense);

            // Calculate projected balance as running balance + remaining income - remaining expense
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
        // Total expected receivables in the period
        $totalReceivables = Receivable::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('remaining_amount');

        // Total expected payables in the period
        $totalPayables = Payable::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('remaining_amount');

        // Overdue receivables
        $overdueReceivables = Receivable::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::now())
            ->sum('remaining_amount');

        // Overdue payables
        $overduePayables = Payable::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::now())
            ->sum('remaining_amount');

        return [
            'totalReceivables' => $totalReceivables,
            'totalPayables' => $totalPayables,
            'expectedBalance' => $totalReceivables - $totalPayables,
            'overdueReceivables' => $overdueReceivables,
            'overduePayables' => $overduePayables,
        ];
    }

    private function getAccountBalances()
    {
        return Account::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->select('id', 'name', 'current_balance')
            ->get();
    }
}
