<?php

namespace App\Services;

use App\Models\RuralProducer;
use App\Support\DataNormalizer;

class RuralProducerService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): RuralProducer
    {
        return RuralProducer::create($this->normalize($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(RuralProducer $ruralProducer, array $data): RuralProducer
    {
        $ruralProducer->update($this->normalize($data));

        return $ruralProducer->refresh();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $data['cpf_cnpj'] = DataNormalizer::normalizeCpfCnpj($data['cpf_cnpj'] ?? null);
        $data['phone'] = DataNormalizer::normalizePhone($data['phone'] ?? null);
        $data['postal_code'] = DataNormalizer::normalizePostalCode($data['postal_code'] ?? null);
        $data['state'] = DataNormalizer::normalizeState($data['state'] ?? null);

        return $data;
    }
}
