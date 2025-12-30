<?php

namespace App\Http\Requests\Rules;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('rule')->accountable->user_id;
    }

    public function rules(): array
    {
        return [
            'search_string' => ['required', 'string', 'max:255'],
            'replace_string' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }

    public function toDto()
    {
        return new \App\Data\Rules\UpdateRuleData(
            searchString: $this->input('search_string'),
            replaceString: $this->input('replace_string'),
            category_id: $this->input('category_id'),
        );
    }
}
