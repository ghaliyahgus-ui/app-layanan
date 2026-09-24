<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requestDate = fake()->dateTimeBetween('-2 months', 'now');
        $activityDate = (clone $requestDate)->modify('+'.fake()->numberBetween(8, 20).' days');

        return [
            'request_number' => 'REQ-'.fake()->unique()->numerify('2026####'),
            'applicant_id' => User::factory()->client(),
            'officer_id' => null,
            'service_type_id' => ServiceType::factory(),
            'letter_number' => fake()->numerify('###/PEM/IX/2026'),
            'request_date' => $requestDate->format('Y-m-d'),
            'activity_date' => $activityDate->format('Y-m-d'),
            'location' => fake()->address(),
            'notes' => fake()->sentence(),
            'status' => ServiceRequestStatus::Submitted,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Submitted,
            'officer_id' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Confirmed,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Scheduled,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function permitIssued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::PermitIssued,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::AwaitingPayment,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Paid,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Completed,
            'officer_id' => User::factory()->officer(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ServiceRequestStatus::Rejected,
            'officer_id' => User::factory()->officer(),
        ]);
    }
}
