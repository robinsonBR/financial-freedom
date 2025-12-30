<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rules\StoreRuleRequest;
use App\Services\Rules\StoreRule;
use Illuminate\Http\RedirectResponse;

class RulesController extends Controller
{
    public function __construct(
        private readonly StoreRule $storeRule,
    ) {}

    public function store(StoreRuleRequest $request): RedirectResponse
    {
        $this->storeRule->execute($request->toDto());

        return redirect()->back();
    }
}
