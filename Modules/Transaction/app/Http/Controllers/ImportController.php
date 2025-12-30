<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CashAccounts\IndexCashAccounts;
use App\Services\CreditCards\IndexCreditCards;
use App\Services\Groups\IndexGroups;
use App\Services\Loans\IndexLoans;
use Modules\Transaction\Services\ImportTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function __construct(
        private readonly IndexGroups $indexGroups,
        private readonly IndexCashAccounts $indexCashAccounts,
        private readonly IndexCreditCards $indexCreditCards,
        private readonly IndexLoans $indexLoans,
        private readonly ImportTransactions $importTransactions,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Transactions/Import/Index', [
            'group' => 'transactions',
            'groups' => fn() => $this->indexGroups->index($request),
            'cashAccounts' => fn() => $this->indexCashAccounts->index(),
            'creditCards' => fn() => $this->indexCreditCards->index(),
            'loans' => fn() => $this->indexLoans->index(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->importTransactions->execute(
            $request->get('account'),
            $request->get('transactions')
        );

        return redirect()->route('transactions.index');
    }
}