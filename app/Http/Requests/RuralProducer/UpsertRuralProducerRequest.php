<?php

namespace App\Http\Requests\RuralProducer;

use App\Rules\CpfCnpj;
use App\Support\DataNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRuralProducerRequest extends FormRequest
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
        $producerId = $this->route('rural_producer')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => [
                'required',
                'string',
                new CpfCnpj(),
                Rule::unique('rural_producers', 'cpf_cnpj')->ignore($producerId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('rural_producers', 'email')->ignore($producerId),
            ],
            'postal_code' => ['required', 'digits:8'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf_cnpj' => DataNormalizer::normalizeCpfCnpj($this->input('cpf_cnpj')),
            'phone' => DataNormalizer::normalizePhone($this->input('phone')),
            'postal_code' => DataNormalizer::normalizePostalCode($this->input('postal_code')),
            'state' => DataNormalizer::normalizeState($this->input('state')),
        ]);
    }
}
