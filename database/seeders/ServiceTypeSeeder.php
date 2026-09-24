<?php

namespace Database\Seeders;

use App\Enums\TariffUnit;
use App\Models\ServiceType;
use App\Models\Tariff;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServiceType::firstOrCreate(
            ['name' => 'Pemangkasan'],
            [
                'requires_permit' => false,
                'is_active' => true,
            ]
        );

        ServiceType::firstOrCreate(
            ['name' => 'Perapian'],
            [
                'requires_permit' => false,
                'is_active' => true,
            ]
        );

        $landLoan = ServiceType::firstOrCreate(
            ['name' => 'Peminjaman Lahan'],
            [
                'requires_permit' => true,
                'is_active' => true,
            ]
        );

        Tariff::firstOrCreate(
            ['service_type_id' => $landLoan->id],
            [
                'amount' => 50000.00,
                'unit' => TariffUnit::PerDay,
                'is_active' => true,
            ]
        );
    }
}
