<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $exams = $this->input('exams', []);

        if (! is_array($exams)) {
            return;
        }

        $this->merge([
            'exams' => array_map(function (mixed $exam): mixed {
                if (! is_array($exam) || ! isset($exam['code'])) {
                    return $exam;
                }

                return [
                    ...$exam,
                    'code' => strtoupper((string) $exam['code']),
                ];
            }, $exams),
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
            'external_service_id' => ['required', 'integer', 'min:1'],
            'requested_at' => ['required', 'date'],
            'patient.name' => ['required', 'string', 'max:255'],
            'patient.document' => ['required', 'string', 'max:50'],
            'patient.sex' => ['required', 'string', 'max:50', Rule::in(['m', 'f'])],
            'patient.birth_date' => ['required', 'date'],
            'exams' => ['required', 'array', 'min:1'],
            'exams.*.code' => ['required', 'string', 'max:80', Rule::in(array_keys(config('exams.fake_results', [])))],
            'requester' => ['required', 'array'],
            'requester.name' => ['required', 'string', 'max:255'],
        ];
    }
}
