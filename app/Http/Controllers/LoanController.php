<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Services\Loans\UpdateLoanAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Transaction\Models\Transaction;

class LoanController extends Controller
{
    public function __construct(
        private readonly UpdateLoanAccount $updateLoanAccount,
    ) {}

    public function show(Request $request, Loan $loan): Response
    {
        $this->authorize('view', $loan);

        $loan->load('institution');

        $transactions = Transaction::query()
            ->with('category')
            ->where('user_id', $request->user()->id)
            ->where('accountable_type', Loan::class)
            ->where('accountable_id', $loan->id)
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('Loans/Show', [
            'group' => 'accounts',
            'loan' => $loan,
            'transactions' => $transactions,
        ]);
    }

    public function update(Request $request, Loan $loan): RedirectResponse
    {
        $this->authorize('update', $loan);
        
        $this->updateLoanAccount->update($request, $loan);

        return redirect()->back();
    }
}
