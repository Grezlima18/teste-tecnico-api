<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('sex')) {
            return;
        }

        $this->merge([
            'sex' => strtolower((string) $this->input('sex')),
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
            'sex' => ['required', 'string', Rule::in(['m', 'f'])],
            'birth_date' => ['required', 'date'],
        ];
    }
}
