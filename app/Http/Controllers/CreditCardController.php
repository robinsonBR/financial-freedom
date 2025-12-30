<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Services\CreditCards\UpdateCreditCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreditCardController extends Controller
{
    public function __construct(
        private readonly UpdateCreditCard $updateCreditCard,
    ) {}

    public function show(CreditCard $creditCard): Response
    {
        $this->authorize('view', $creditCard);

        return Inertia::render('CreditCards/Show', [
            'group' => 'accounts',
            'creditCard' => $creditCard,
        ]);
    }

    public function update(Request $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorize('update', $creditCard);
        
        $this->updateCreditCard->update($request, $creditCard);
        
        return redirect()->back();
    }
}