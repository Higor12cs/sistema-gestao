<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        return inertia('Dashboard/Index', [
            'metrics' => $this->getMetrics($startDate, $endDate),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    public function getMetrics($startDate, $endDate)
    {
        return [
            'summary' => $this->getSummaryData($startDate, $endDate),
            'salesData' => $this->getSalesData($startDate, $endDate),
            'purchasesData' => $this->getPurchasesData($startDate, $endDate),
            'financialData' => $this->getFinancialData($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'topCustomers' => $this->getTopCustomers($startDate, $endDate),
            'topSellers' => $this->getTopSellers($startDate, $endDate),
            'salesByDay' => $this->getSalesByDay($startDate, $endDate),
        ];
    }

    private function getSummaryData($startDate, $endDate)
    {
        $orderCount = Order::whereBetween('issue_date', [$startDate, $endDate])->count();
        $purchaseCount = Purchase::whereBetween('issue_date', [$startDate, $endDate])->count();
        $customerCount = Customer::where('active', true)->count();
        $supplierCount = Supplier::where('active', true)->count();
        $productCount = Product::where('active', true)->count();

        $totalSales = Order::whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_price');

        $totalPurchases = Purchase::whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_cost');

        $incomeTransactions = Transaction::where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $expenseTransactions = Transaction::where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $balance = $incomeTransactions - $expenseTransactions;

        return [
            'orderCount' => $orderCount,
            'purchaseCount' => $purchaseCount,
            'customerCount' => $customerCount,
            'supplierCount' => $supplierCount,
            'productCount' => $productCount,
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'balance' => $balance,
        ];
    }

    private function getSalesData($startDate, $endDate)
    {
        $totalSales = Order::whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_price');

        $orderCount = Order::whereBetween('issue_date', [$startDate, $endDate])->count();

        $averageTicket = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $totalItems = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('issue_date', [$startDate, $endDate]);
        })->sum('quantity');

        $previousStart = Carbon::parse($startDate)->subDays(Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate)));
        $previousEnd = Carbon::parse($startDate)->subDay();

        $previousSales = Order::whereBetween('issue_date', [$previousStart, $previousEnd])
            ->sum('total_price');

        $salesGrowth = $previousSales > 0
            ? (($totalSales - $previousSales) / $previousSales) * 100
            : 0;

        return [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageTicket' => $averageTicket,
            'totalItems' => $totalItems,
            'salesGrowth' => $salesGrowth,
        ];
    }

    private function getPurchasesData($startDate, $endDate)
    {
        $totalPurchases = Purchase::whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_cost');

        $purchaseCount = Purchase::whereBetween('issue_date', [$startDate, $endDate])->count();

        $previousStart = Carbon::parse($startDate)->subDays(Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate)));
        $previousEnd = Carbon::parse($startDate)->subDay();

        $previousPurchases = Purchase::whereBetween('issue_date', [$previousStart, $previousEnd])
            ->sum('total_cost');

        $purchaseGrowth = $previousPurchases > 0
            ? (($totalPurchases - $previousPurchases) / $previousPurchases) * 100
            : 0;

        return [
            'totalPurchases' => $totalPurchases,
            'purchaseCount' => $purchaseCount,
            'purchaseGrowth' => $purchaseGrowth,
        ];
    }

    private function getFinancialData($startDate, $endDate)
    {
        $pendingReceivables = Receivable::where('due_date', '<=', $endDate)
            ->where('due_date', '>=', Carbon::now()->startOfDay())
            ->where('status', '!=', 'paid')
            ->sum('remaining_amount');

        $overdueReceivables = Receivable::where('due_date', '<', Carbon::now()->startOfDay())
            ->where('status', '!=', 'paid')
            ->sum('remaining_amount');

        $pendingPayables = Payable::where('due_date', '<=', $endDate)
            ->where('due_date', '>=', Carbon::now()->startOfDay())
            ->where('status', '!=', 'paid')
            ->sum('remaining_amount');

        $overduePayables = Payable::where('due_date', '<', Carbon::now()->startOfDay())
            ->where('status', '!=', 'paid')
            ->sum('remaining_amount');

        $accountBalances = Account::where('active', true)
            ->sum('current_balance');

        return [
            'pendingReceivables' => $pendingReceivables,
            'overdueReceivables' => $overdueReceivables,
            'pendingPayables' => $pendingPayables,
            'overduePayables' => $overduePayables,
            'accountBalances' => $accountBalances,
        ];
    }

    private function getTopProducts($startDate, $endDate)
    {
        return OrderItem::select(
            'products.id',
            'products.name',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.total_price) as total_revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.issue_date', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTopCustomers($startDate, $endDate)
    {
        return Order::select(
            'customers.id',
            DB::raw("customers.first_name || ' ' || COALESCE(customers.last_name, '') as name"),
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('SUM(orders.total_price) as total_spent')
        )
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->whereBetween('orders.issue_date', [$startDate, $endDate])
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTopSellers($startDate, $endDate)
    {
        return Order::select(
            'sellers.id',
            DB::raw('sellers.name as name'),
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('SUM(orders.total_price) as total_spent')
        )
            ->join('sellers', 'orders.seller_id', '=', 'sellers.id')
            ->whereBetween('orders.issue_date', [$startDate, $endDate])
            ->groupBy('sellers.id', 'sellers.name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();
    }

    private function getSalesByDay($startDate, $endDate)
    {
        $days = ceil($startDate->diffInDays($endDate) + 1);

        if ($days > 365) {
            return $this->getSalesByMonth($startDate, $endDate);
        }

        if ($days > 60) {
            return $this->getSalesByWeek($startDate, $endDate);
        }

        if ($days < 1) {
            $days = 1;
        }

        $salesByDay = Order::select(
            DB::raw('DATE(issue_date) as date'),
            DB::raw('SUM(total_price) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->total;
            });

        $expensesByDay = Purchase::select(
            DB::raw('DATE(issue_date) as date'),
            DB::raw('SUM(total_cost) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(function ($item) {
                return $item->total;
            });

        $period = collect(range(0, $days - 1))->map(function ($i) use ($startDate) {
            return $startDate->copy()->addDays($i)->format('Y-m-d');
        });

        $result = [];
        $cumulativeSales = 0;
        $cumulativeExpenses = 0;
        foreach ($period as $date) {
            $cumulativeSales += $salesByDay[$date] ?? 0;
            $cumulativeExpenses += $expensesByDay[$date] ?? 0;
            $result[] = [
                'date' => $date,
                'sales' => $cumulativeSales,
                'expenses' => $cumulativeExpenses,
            ];
        }

        return $result;
    }

    private function getSalesByWeek($startDate, $endDate)
    {
        $salesByWeek = Order::select(
            DB::raw("TO_CHAR(issue_date, 'IYYYIW') as yearweek"),
            DB::raw('MIN(DATE(issue_date)) as start_date'),
            DB::raw('SUM(total_price) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        $expensesByWeek = Purchase::select(
            DB::raw("TO_CHAR(issue_date, 'IYYYIW') as yearweek"),
            DB::raw('SUM(total_cost) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get()
            ->keyBy('yearweek')
            ->map(function ($item) {
                return $item->total;
            });

        $result = [];
        $cumulativeSales = 0;
        $cumulativeExpenses = 0;
        foreach ($salesByWeek as $week) {
            $cumulativeSales += $week->total;
            $cumulativeExpenses += $expensesByWeek[$week->yearweek] ?? 0;
            $result[] = [
                'date' => $week->start_date,
                'sales' => $cumulativeSales,
                'expenses' => $cumulativeExpenses,
            ];
        }

        return $result;
    }

    private function getSalesByMonth($startDate, $endDate)
    {
        $salesByMonth = Order::select(
            DB::raw("TO_CHAR(issue_date, 'YYYY-MM-01') as month"),
            DB::raw('SUM(total_price) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->total;
            });

        $expensesByMonth = Purchase::select(
            DB::raw("TO_CHAR(issue_date, 'YYYY-MM-01') as month"),
            DB::raw('SUM(total_cost) as total')
        )
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->total;
            });

        $startMonth = Carbon::parse($startDate)->startOfMonth();
        $endMonth = Carbon::parse($endDate)->startOfMonth();
        $months = $endMonth->diffInMonths($startMonth) + 1;

        $result = [];
        $cumulativeSales = 0;
        $cumulativeExpenses = 0;
        for ($i = 0; $i < $months; $i++) {
            $month = $startMonth->copy()->addMonths($i)->format('Y-m-01');
            $cumulativeSales += $salesByMonth[$month] ?? 0;
            $cumulativeExpenses += $expensesByMonth[$month] ?? 0;
            $result[] = [
                'date' => $month,
                'sales' => $cumulativeSales,
                'expenses' => $cumulativeExpenses,
            ];
        }

        return $result;
    }
}
