<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Services\CashAccounts\UpdateCashAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Transaction\Models\Transaction;

class CashAccountController extends Controller
{
    public function show(Request $request, CashAccount $cashAccount): Response
    {
        $user = $request->user();

        if ($cashAccount->user_id !== $user->id) {
            abort(404);
        }

        $cashAccount->load('institution');

        $transactions = Transaction::query()
            ->with('category')
            ->where('user_id', $user->id)
            ->where('accountable_type', CashAccount::class)
            ->where('accountable_id', $cashAccount->id)
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('Cash/Show', [
            'group' => 'accounts',
            'cashAccount' => $cashAccount,
            'transactions' => $transactions,
        ]);
    }

    public function update(Request $request, CashAccount $cashAccount): RedirectResponse
    {
        (new UpdateCashAccount($request, $cashAccount))->update();

        return redirect()->back();
    }

    // public function destroy( Account $account ): RedirectResponse
    // {
    //     ( new DeleteAccount() )->delete( $account );
    //     return redirect()->back();
    // }
}