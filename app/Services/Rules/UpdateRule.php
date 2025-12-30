<?php

namespace App\Services\Rules;

use App\Models\Rule;

class UpdateRule
{
    public function execute($dto, Rule $rule): Rule
    {
        $rule->update([
            'search_string' => $dto->searchString,
            'replace_string' => $dto->replaceString,
            'category_id' => $dto->category_id,
        ]);

        return $rule->fresh();
    }
}
