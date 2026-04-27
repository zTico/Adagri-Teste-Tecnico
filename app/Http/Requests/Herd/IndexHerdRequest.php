<?php

namespace App\Http\Requests\Herd;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexHerdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'species' => ['nullable', Rule::in(HerdSpecies::values())],
            'purpose' => ['nullable', Rule::in(HerdPurpose::values())],
            'farm_id' => ['nullable', 'uuid', 'exists:farms,id'],
            'rural_producer_id' => ['nullable', 'uuid', 'exists:rural_producers,id'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
