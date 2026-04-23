<?php

namespace App\Http\Requests\Farm;

use App\Support\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $farmId = $this->route('farm')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'state_registration' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('farms', 'state_registration')->ignore($farmId),
            ],
            'total_area' => ['required', 'numeric', 'gt:0'],
            'rural_producer_id' => ['required', 'integer', 'exists:rural_producers,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'state' => DataNormalizer::normalizeState($this->input('state')),
        ]);
    }
}
