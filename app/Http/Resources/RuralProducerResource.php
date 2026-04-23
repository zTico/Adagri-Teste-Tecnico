<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuralProducerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cpf_cnpj' => $this->cpf_cnpj,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => [
                'postal_code' => $this->postal_code,
                'street' => $this->street,
                'number' => $this->number,
                'complement' => $this->complement,
                'district' => $this->district,
                'city' => $this->city,
                'state' => $this->state,
            ],
            'farms_count' => isset($this->farms_count) ? (int) $this->farms_count : null,
            'farms' => FarmResource::collection($this->whenLoaded('farms')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
