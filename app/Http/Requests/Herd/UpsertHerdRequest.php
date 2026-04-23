<?php

namespace App\Http\Requests\Herd;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertHerdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'species' => ['required', Rule::in(HerdSpecies::values())],
            'quantity' => ['required', 'integer', 'min:1'],
            'purpose' => ['required', Rule::in(HerdPurpose::values())],
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
        ];
    }
}
