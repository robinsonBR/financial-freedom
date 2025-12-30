<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CashAccounts\IndexCashAccounts;
use App\Services\CreditCards\IndexCreditCards;
use App\Services\Groups\IndexGroups;
use App\Services\Loans\IndexLoans;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Transaction\Http\Requests\StoreTransactionRequest;
use Modules\Transaction\Http\Requests\UpdateTransactionRequest;
use Modules\Transaction\Models\Transaction;
use Modules\Transaction\Services\IndexTransactions;
use Modules\Transaction\Services\StoreTransaction;
use Modules\Transaction\Services\UpdateTransaction;

class TransactionController extends Controller
{
    public function __construct(
        private readonly IndexTransactions $indexTransactions,
        private readonly IndexGroups $indexGroups,
        private readonly IndexCashAccounts $indexCashAccounts,
        private readonly IndexCreditCards $indexCreditCards,
        private readonly IndexLoans $indexLoans,
        private readonly StoreTransaction $storeTransaction,
        private readonly UpdateTransaction $updateTransaction,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Transactions/Index', [
            'group' => 'transactions',
            'transactions' => fn() => $this->indexTransactions->execute($request),
            'groups' => fn() => $this->indexGroups->index($request),
            'cashAccounts' => fn() => $this->indexCashAccounts->index(),
            'creditCards' => fn() => $this->indexCreditCards->index(),
            'loans' => fn() => $this->indexLoans->index(),
            'filters' => $request->all()
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $this->storeTransaction->execute($request);

        return redirect()->route('transactions.index');
    }

    public function show(Transaction $transaction): Response
    {
        $this->authorize('view', $transaction);
        
        $transaction->load('accountable');

        return Inertia::modal('Transactions/Show', [
            'transaction' => $transaction
        ])
        ->baseRoute('transactions.index');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        
        $this->updateTransaction->execute($request, $transaction);

        return redirect()->route('transactions.index');
    }
}