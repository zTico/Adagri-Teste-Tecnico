<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'state_registration' => $this->state_registration,
            'total_area' => (float) $this->total_area,
            'herds_count' => isset($this->herds_count) ? (int) $this->herds_count : null,
            'total_animals' => isset($this->total_animals) ? (int) $this->total_animals : null,
            'rural_producer' => $this->whenLoaded('ruralProducer', fn (): array => [
                'id' => $this->ruralProducer->id,
                'name' => $this->ruralProducer->name,
            ]),
            'herds' => HerdResource::collection($this->whenLoaded('herds')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
