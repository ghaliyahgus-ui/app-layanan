<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'type' => fake()->randomElement([DocumentType::IdCard, DocumentType::RequestLetter]),
            'file_name' => fake()->word().'.pdf',
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
        ];
    }

    public function idCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::IdCard,
            'file_name' => 'ktp_'.fake()->word().'.jpg',
            'file_path' => 'documents/ktp/'.fake()->uuid().'.jpg',
        ]);
    }

    public function requestLetter(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::RequestLetter,
            'file_name' => 'surat_permohonan_'.fake()->word().'.pdf',
            'file_path' => 'documents/letters/'.fake()->uuid().'.pdf',
        ]);
    }
}
