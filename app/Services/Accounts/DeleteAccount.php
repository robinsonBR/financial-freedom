<?php

namespace App\Services\Accounts;

use App\Models\CashAccount;
use App\Models\CreditCard;
use App\Models\Loan;

class DeleteAccount
{
    public function delete(CashAccount|CreditCard|Loan $account): void
    {
        // Delete all transactions associated with this account
        $account->transactions()->delete();
        
        // Delete the account
        $account->delete();
    }
}
