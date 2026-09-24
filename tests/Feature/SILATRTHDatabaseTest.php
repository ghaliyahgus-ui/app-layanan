<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Enums\ServiceRequestStatus;
use App\Enums\TariffUnit;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\Schedule;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\StatusLog;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SILATRTHDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_silat_rth_models_and_verify_relations(): void
    {
        // 1. User (Applicant & Officer)
        $applicant = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret123',
            'phone' => '08123456789',
            'address' => 'Jl. Soekarno Hatta No. 10, Trenggalek',
            'role' => UserRole::Client,
        ]);

        $officer = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@trenggalekkab.go.id',
            'password' => 'secret123',
            'phone' => '08987654321',
            'address' => 'Kantor DLH Trenggalek',
            'role' => UserRole::Officer,
        ]);

        $this->assertTrue($applicant->isClient());
        $this->assertTrue($officer->isOfficer());

        // 2. ServiceType & Tariff
        $serviceType = ServiceType::create([
            'name' => 'Peminjaman Lahan',
            'requires_permit' => true,
            'is_active' => true,
        ]);

        $tariff = Tariff::create([
            'service_type_id' => $serviceType->id,
            'amount' => 50000.00,
            'unit' => TariffUnit::PerDay,
            'is_active' => true,
        ]);

        // 3. ServiceRequest
        $serviceRequest = ServiceRequest::create([
            'request_number' => 'REQ-2026-0001',
            'applicant_id' => $applicant->id,
            'officer_id' => $officer->id,
            'service_type_id' => $serviceType->id,
            'letter_number' => '005/PAN-HUT/2026',
            'request_date' => now()->toDateString(),
            'activity_date' => now()->addDays(10)->toDateString(),
            'location' => 'Alun-Alun Trenggalek sisi utara',
            'notes' => 'Peminjaman untuk kegiatan pameran seni',
            'status' => ServiceRequestStatus::Submitted,
        ]);

        // 4. Document
        $document = Document::create([
            'service_request_id' => $serviceRequest->id,
            'type' => DocumentType::IdCard,
            'file_name' => 'ktp_budi.jpg',
            'file_path' => 'documents/ktp_budi.jpg',
        ]);

        // 5. Schedule
        $schedule = Schedule::create([
            'service_request_id' => $serviceRequest->id,
            'scheduled_date' => now()->addDays(10)->toDateString(),
            'notes' => 'Petugas standby pkl 08:00 WIB',
        ]);

        // 6. Permit
        $permit = Permit::create([
            'service_request_id' => $serviceRequest->id,
            'permit_number' => 'IZN/DLH/2026/001',
            'issued_date' => now()->toDateString(),
            'valid_until' => now()->addDays(11)->toDateString(),
            'file_path' => 'permits/surat_izin_001.pdf',
        ]);

        // 7. Payment
        $payment = Payment::create([
            'permit_id' => $permit->id,
            'tariff_id' => $tariff->id,
            'amount' => 50000.00,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'proof_path' => 'payments/bukti_bayar_001.jpg',
        ]);

        // 8. StatusLog
        $statusLog = StatusLog::create([
            'service_request_id' => $serviceRequest->id,
            'changed_by' => $officer->id,
            'old_status' => ServiceRequestStatus::Submitted,
            'new_status' => ServiceRequestStatus::Confirmed,
        ]);

        // Assertions for relationships
        $this->assertEquals($applicant->id, $serviceRequest->applicant->id);
        $this->assertEquals($officer->id, $serviceRequest->officer->id);
        $this->assertEquals($serviceType->id, $serviceRequest->serviceType->id);
        $this->assertCount(1, $serviceRequest->documents);
        $this->assertEquals(DocumentType::IdCard, $serviceRequest->documents->first()->type);
        $this->assertEquals($schedule->id, $serviceRequest->schedule->id);
        $this->assertEquals($permit->id, $serviceRequest->permit->id);
        $this->assertEquals($payment->id, $permit->payment->id);
        $this->assertEquals($tariff->id, $payment->tariff->id);
        $this->assertCount(1, $serviceRequest->statusLogs);
        $this->assertEquals($officer->id, $statusLog->changedBy->id);

        // Assertions for Enums
        $this->assertSame(ServiceRequestStatus::Submitted, $serviceRequest->status);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame(TariffUnit::PerDay, $tariff->unit);
    }
}
