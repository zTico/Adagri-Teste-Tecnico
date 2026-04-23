<?php

namespace App\Services;

use App\Models\Farm;
use App\Support\DataNormalizer;

class FarmService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Farm
    {
        return Farm::create($this->normalize($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Farm $farm, array $data): Farm
    {
        $farm->update($this->normalize($data));

        return $farm->refresh();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $data['state'] = DataNormalizer::normalizeState($data['state'] ?? null);

        return $data;
    }
}
