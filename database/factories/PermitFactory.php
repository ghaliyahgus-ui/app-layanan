<?php

namespace Database\Factories;

use App\Models\Permit;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permit>
 */
class PermitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issued = fake()->dateTimeBetween('-1 month', 'now');
        $validUntil = (clone $issued)->modify('+30 days');

        return [
            'service_request_id' => ServiceRequest::factory()->permitIssued(),
            'permit_number' => '660/'.fake()->unique()->numerify('###').'/IZIN-RTH/DLH/2026',
            'issued_date' => $issued->format('Y-m-d'),
            'valid_until' => $validUntil->format('Y-m-d'),
            'file_path' => 'permits/'.fake()->uuid().'.pdf',
        ];
    }
}
