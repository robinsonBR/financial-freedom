<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Transaction\Models\Transaction;

class ReportsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get date range (default to last 6 months)
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Monthly income vs expenses trend
        $monthlyTrend = Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, type, SUM(amount) as total')
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(function ($transactions, $month) {
                return [
                    'month' => $month,
                    'income' => $transactions->where('type', 'credit')->sum('total') ?? 0,
                    'expenses' => $transactions->where('type', 'debit')->sum('total') ?? 0,
                    'net' => ($transactions->where('type', 'credit')->sum('total') ?? 0) - 
                             ($transactions->where('type', 'debit')->sum('total') ?? 0),
                ];
            })
            ->values();

        // Category spending breakdown (top 10 categories by spending)
        $categoryBreakdown = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                $category = Category::find($transaction->category_id);
                return [
                    'category' => $category ? $category->name : 'Unknown',
                    'color' => $category ? $category->color : 'gray',
                    'total' => $transaction->total,
                ];
            });

        // Summary statistics
        $totalIncome = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'credit')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpenses = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $avgMonthlyIncome = $monthlyTrend->avg('income');
        $avgMonthlyExpenses = $monthlyTrend->avg('expenses');

        return Inertia::render('Reports/Index', [
            'group' => 'reports',
            'monthlyTrend' => $monthlyTrend,
            'categoryBreakdown' => $categoryBreakdown,
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'netSavings' => $totalIncome - $totalExpenses,
                'avgMonthlyIncome' => $avgMonthlyIncome,
                'avgMonthlyExpenses' => $avgMonthlyExpenses,
                'savingsRate' => $totalIncome > 0 ? (($totalIncome - $totalExpenses) / $totalIncome) * 100 : 0,
            ],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
