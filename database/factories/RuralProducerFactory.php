<?php

namespace Database\Factories;

use App\Models\RuralProducer;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuralProducerFactory extends Factory
{
    protected $model = RuralProducer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'cpf_cnpj' => $this->validCpf(),
            'phone' => fake()->numerify('55###########'),
            'email' => fake()->unique()->safeEmail(),
            'postal_code' => fake()->numerify('########'),
            'street' => fake()->streetName(),
            'number' => (string) fake()->buildingNumber(),
            'complement' => fake()->optional()->secondaryAddress(),
            'district' => fake()->citySuffix(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'GO', 'MT', 'MS', 'MG', 'PR']),
        ];
    }

    private function validCpf(): string
    {
        $numbers = [];

        for ($i = 0; $i < 9; $i++) {
            $numbers[] = random_int(0, 9);
        }

        $numbers[] = $this->cpfDigit($numbers, 10);
        $numbers[] = $this->cpfDigit($numbers, 11);

        return implode('', $numbers);
    }

    private function cpfDigit(array $numbers, int $factor): int
    {
        $sum = 0;

        foreach ($numbers as $number) {
            $sum += $number * $factor--;
        }

        $remainder = ($sum * 10) % 11;

        return $remainder === 10 ? 0 : $remainder;
    }
}
