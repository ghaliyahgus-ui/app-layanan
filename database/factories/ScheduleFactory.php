<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory()->scheduled(),
            'scheduled_date' => fake()->dateTimeBetween('+1 week', '+3 weeks')->format('Y-m-d'),
            'notes' => fake()->sentence(),
        ];
    }
}
