<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rules\StoreRuleRequest;
use App\Http\Requests\Rules\UpdateRuleRequest;
use App\Models\Category;
use App\Models\Rule;
use App\Services\Rules\DeleteRule;
use App\Services\Rules\StoreRule;
use App\Services\Rules\UpdateRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RulesController extends Controller
{
    public function __construct(
        private readonly StoreRule $storeRule,
        private readonly UpdateRule $updateRule,
        private readonly DeleteRule $deleteRule,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $rules = Rule::query()
            ->whereHasMorph('accountable', ['App\Models\CashAccount', 'App\Models\CreditCard', 'App\Models\Loan'], function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['category', 'accountable'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $categories = Category::where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return Inertia::render('Rules/Index', [
            'group' => 'rules',
            'rules' => $rules,
            'categories' => $categories,
        ]);
    }

    public function store(StoreRuleRequest $request): RedirectResponse
    {
        $this->storeRule->execute($request->toDto());

        return redirect()->back();
    }

    public function update(UpdateRuleRequest $request, Rule $rule): RedirectResponse
    {
        $this->updateRule->execute($request->toDto(), $rule);

        return redirect()->back();
    }

    public function destroy(Rule $rule): RedirectResponse
    {
        $this->deleteRule->execute($rule);

        return redirect()->back();
    }
}
