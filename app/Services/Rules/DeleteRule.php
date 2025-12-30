<?php

namespace App\Services\Rules;

use App\Models\Rule;

class DeleteRule
{
    public function execute(Rule $rule): void
    {
        $rule->delete();
    }
}
