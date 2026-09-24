<?php

namespace Database\Factories;

use App\Enums\TariffUnit;
use App\Models\ServiceType;
use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tariff>
 */
class TariffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_type_id' => ServiceType::factory()->requiresPermit(),
            'amount' => fake()->randomElement([50000.00, 75000.00, 100000.00, 250000.00]),
            'unit' => TariffUnit::PerDay,
            'is_active' => true,
        ];
    }

    public function perActivity(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit' => TariffUnit::PerActivity,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
