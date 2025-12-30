<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Models\Institution;
use App\Services\Accounts\StoreAccount;
use App\Services\CashAccounts\IndexCashAccounts;
use App\Services\CreditCards\IndexCreditCards;
use App\Services\Loans\IndexLoans;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private readonly IndexCashAccounts $indexCashAccounts,
        private readonly IndexCreditCards $indexCreditCards,
        private readonly IndexLoans $indexLoans,
        private readonly StoreAccount $storeAccount,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Accounts/Index', [
            'group' => 'accounts',
            'cashAccounts' => fn() => $this->indexCashAccounts->index(),
            'creditCards' => fn() => $this->indexCreditCards->index(),
            'loans' => fn() => $this->indexLoans->index(),
            'institutions' => fn() => Institution::orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->storeAccount->store($request);
        
        return redirect()->back();
    }
}