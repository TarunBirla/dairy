<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Branch;
use App\Models\PosOrder;
use App\Models\MilkCollection;
use App\Models\AuditLog;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'branch', 'recorder']);

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('expense_date', $date->year)->whereMonth('expense_date', $date->month);
        }

        $expenses = $query->latest()->paginate(20)->withQueryString();
        $totalExpenses = Expense::sum('amount');
        $categories = ExpenseCategory::all();
        $branches = Branch::where('status', 'active')->get();

        return view('expenses.index', compact('expenses', 'totalExpenses', 'categories', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,bank_transfer',
            'vendor_name' => 'nullable|string',
            'description' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'recorded_by' => Auth::id(),
            'approved_by' => Auth::id(),
            'status' => 'approved',
        ]));

        AuditLog::log('Created Expense', 'Expense', $expense->id, ['amount' => $validated['amount']]);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function storeCategoryAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category = ExpenseCategory::firstOrCreate(
            ['name' => trim($validated['name'])],
            [
                'slug' => \Illuminate\Support\Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Expense category saved successfully'
        ]);
    }

    public function profitLoss(Request $request)
    {
        $month = $request->get('month', Carbon::today()->format('Y-m'));
        $date = Carbon::parse($month . '-01');
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        // Revenue: POS Sales + Subscription Deliveries
        $posRevenue = PosOrder::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $milkProcurementCost = MilkCollection::whereBetween('collection_date', [$start, $end])->sum('net_amount');
        $operatingExpenses = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

        $totalRevenue = $posRevenue;
        $totalCosts = $milkProcurementCost + $operatingExpenses;
        $netProfit = $totalRevenue - $totalCosts;

        $expenseBreakdown = Expense::whereBetween('expense_date', [$start, $end])
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name, sum(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->get();

        return view('expenses.profit_loss', compact('month', 'posRevenue', 'milkProcurementCost', 'operatingExpenses', 'totalRevenue', 'totalCosts', 'netProfit', 'expenseBreakdown'));
    }
}
