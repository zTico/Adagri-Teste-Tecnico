<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\RuralProducer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    protected $model = Farm::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Farm',
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'GO', 'MT', 'MS', 'MG', 'PR']),
            'state_registration' => strtoupper(fake()->bothify('SR####??')),
            'total_area' => fake()->randomFloat(2, 40, 1200),
            'rural_producer_id' => RuralProducer::factory(),
        ];
    }
}
