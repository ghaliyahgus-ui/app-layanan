<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'permit_id' => Permit::factory(),
            'tariff_id' => Tariff::factory(),
            'amount' => 50000.00,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
            'proof_path' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'proof_path' => 'payments/proof_'.fake()->uuid().'.jpg',
        ]);
    }
}
