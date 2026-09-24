<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Admin Sistem
        User::firstOrCreate(
            ['email' => 'admin@trenggalekkab.go.id'],
            [
                'name' => 'Administrator SILAT RTH',
                'password' => $defaultPassword,
                'phone' => '081234567890',
                'address' => 'Kantor Dinas Lingkungan Hidup Kab. Trenggalek, Jl. K.S. Tubun No. 12',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        // 2. Petugas Layanan (Staf Bidang Tata Lingkungan)
        User::firstOrCreate(
            ['email' => 'petugas@trenggalekkab.go.id'],
            [
                'name' => 'Ghaliyah Ayu Guswami (Petugas Layanan)',
                'password' => $defaultPassword,
                'phone' => '081234567891',
                'address' => 'Bidang Tata Lingkungan & PKH, Dinas Lingkungan Hidup Kab. Trenggalek',
                'role' => UserRole::Officer,
                'email_verified_at' => now(),
            ]
        );

        // 3. Pimpinan (Kepala Dinas Lingkungan Hidup)
        User::firstOrCreate(
            ['email' => 'pimpinan@trenggalekkab.go.id'],
            [
                'name' => 'Kepala Dinas Lingkungan Hidup Kab. Trenggalek',
                'password' => $defaultPassword,
                'phone' => '081234567892',
                'address' => 'Kantor Dinas Lingkungan Hidup Kab. Trenggalek',
                'role' => UserRole::Leader,
                'email_verified_at' => now(),
            ]
        );

        // 4. Klien / Pemohon (Masyarakat & Instansi)
        User::firstOrCreate(
            ['email' => 'klien@trenggalekkab.go.id'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => $defaultPassword,
                'phone' => '081234567893',
                'address' => 'Jl. Soekarno Hatta No. 45, Kel. Kelutan, Trenggalek',
                'role' => UserRole::Client,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'smpn1trenggalek@example.com'],
            [
                'name' => 'Budi Santoso, S.Pd (SMPN 1 Trenggalek)',
                'password' => $defaultPassword,
                'phone' => '081398765432',
                'address' => 'Jl. HOS Cokroaminoto No. 22, Kel. Surodakan, Trenggalek',
                'role' => UserRole::Client,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'siti.aminah@example.com'],
            [
                'name' => 'Siti Aminah',
                'password' => $defaultPassword,
                'phone' => '082155443322',
                'address' => 'RT 04 RW 02 Kel. Surodakan, Kec. Trenggalek',
                'role' => UserRole::Client,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'dewi.lestari@example.com'],
            [
                'name' => 'Dewi Lestari (Komunitas Kreatif Trenggalek)',
                'password' => $defaultPassword,
                'phone' => '085712345678',
                'address' => 'Dusun Krajan, Desa Karangan, Kec. Karangan, Trenggalek',
                'role' => UserRole::Client,
                'email_verified_at' => now(),
            ]
        );
    }
}
