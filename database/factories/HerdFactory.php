<?php

namespace Database\Factories;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use App\Models\Farm;
use App\Models\Herd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Herd>
 */
class HerdFactory extends Factory
{
    protected $model = Herd::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'species' => fake()->randomElement(HerdSpecies::values()),
            'quantity' => fake()->numberBetween(10, 500),
            'purpose' => fake()->randomElement(HerdPurpose::values()),
            'farm_id' => Farm::factory(),
        ];
    }
}
