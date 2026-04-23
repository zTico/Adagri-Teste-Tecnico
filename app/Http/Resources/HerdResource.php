<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HerdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'species' => $this->species?->value,
            'quantity' => $this->quantity,
            'purpose' => $this->purpose?->value,
            'farm' => $this->whenLoaded('farm', fn (): array => [
                'id' => $this->farm->id,
                'name' => $this->farm->name,
                'rural_producer_id' => $this->farm->rural_producer_id,
            ]),
            'rural_producer' => $this->whenLoaded('farm', function (): ?array {
                if (! $this->farm->relationLoaded('ruralProducer') || ! $this->farm->ruralProducer) {
                    return null;
                }

                return [
                    'id' => $this->farm->ruralProducer->id,
                    'name' => $this->farm->ruralProducer->name,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
