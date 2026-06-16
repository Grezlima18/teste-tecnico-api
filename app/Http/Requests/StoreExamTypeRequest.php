<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamTypeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('code')) {
            return;
        }

        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', Rule::unique('exams', 'code')],
            'is_external' => ['sometimes', 'boolean'],
        ];
    }
}
