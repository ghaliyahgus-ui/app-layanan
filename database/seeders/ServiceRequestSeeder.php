<?php

namespace Database\Seeders;

use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Enums\ServiceRequestStatus;
use App\Enums\TariffUnit;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\Schedule;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\StatusLog;
use App\Models\Tariff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officer = User::where('email', 'petugas@trenggalekkab.go.id')->first()
            ?? User::where('role', 'officer')->first();

        $clientAhmad = User::where('email', 'klien@trenggalekkab.go.id')->first();
        $clientBudi = User::where('email', 'smpn1trenggalek@example.com')->first();
        $clientSiti = User::where('email', 'siti.aminah@example.com')->first();
        $clientDewi = User::where('email', 'dewi.lestari@example.com')->first();

        $trimmingType = ServiceType::where('name', 'Pemangkasan')->first();
        $tidyingType = ServiceType::where('name', 'Perapian')->first();
        $landLoanType = ServiceType::where('name', 'Peminjaman Lahan')->first();

        $tariffDay = Tariff::where('service_type_id', $landLoanType?->id)
            ->where('unit', TariffUnit::PerDay)
            ->first();

        $tariffActivity = Tariff::where('service_type_id', $landLoanType?->id)
            ->where('unit', TariffUnit::PerActivity)
            ->first();

        $now = Carbon::now();

        // ---------------------------------------------------------------------
        // 1. Permohonan Baru Diajukan (Submitted) - Pemangkasan Pohon
        // ---------------------------------------------------------------------
        $req1 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0001'],
            [
                'applicant_id' => $clientAhmad->id,
                'officer_id' => null,
                'service_type_id' => $trimmingType->id,
                'letter_number' => '005/RT05/IX/2026',
                'request_date' => $now->copy()->subDays(2)->format('Y-m-d'),
                'activity_date' => $now->copy()->addDays(8)->format('Y-m-d'),
                'location' => 'Jalur Hijau Depan Rumah No. 45, Jl. Soekarno Hatta, Kel. Kelutan',
                'notes' => 'Pohon angsana di tepi jalan sudah sangat rimbun dan rantingnya menyentuh kabel listrik PLN.',
                'status' => ServiceRequestStatus::Submitted,
            ]
        );
        $this->attachStandardDocuments($req1, 'ktp_ahmad_fauzi.jpg', 'surat_pengantar_rt05.pdf');
        $this->logStatusChange($req1, null, ServiceRequestStatus::Submitted, $clientAhmad->id, $req1->created_at);

        // ---------------------------------------------------------------------
        // 2. Permohonan Dikonfirmasi (Confirmed) - Perapian RTH Instansi Sekolah
        // ---------------------------------------------------------------------
        $req2 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0002'],
            [
                'applicant_id' => $clientBudi->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $tidyingType->id,
                'letter_number' => '421/045/SMPN1/IX/2026',
                'request_date' => $now->copy()->subDays(4)->format('Y-m-d'),
                'activity_date' => $now->copy()->addDays(10)->format('Y-m-d'),
                'location' => 'Area Jalur Hijau Samping Gerbang SMPN 1 Trenggalek, Jl. HOS Cokroaminoto',
                'notes' => 'Perapian semak perdu dan rumput liar di sepanjang trotoar sekolah menjelang penilaian Adiwiyata.',
                'status' => ServiceRequestStatus::Confirmed,
            ]
        );
        $this->attachStandardDocuments($req2, 'ktp_budi_santoso.jpg', 'surat_permohonan_smpn1.pdf');
        $this->logStatusChange($req2, null, ServiceRequestStatus::Submitted, $clientBudi->id, $now->copy()->subDays(4));
        if ($officer) {
            $this->logStatusChange($req2, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(2));
        }

        // ---------------------------------------------------------------------
        // 3. Permohonan Dijadwalkan (Scheduled) - Pemangkasan Pohon Warga
        // ---------------------------------------------------------------------
        $req3Date = $now->copy()->addDays(5)->format('Y-m-d');
        $req3 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0003'],
            [
                'applicant_id' => $clientSiti->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $trimmingType->id,
                'letter_number' => '012/RW02/SRD/IX/2026',
                'request_date' => $now->copy()->subDays(6)->format('Y-m-d'),
                'activity_date' => $req3Date,
                'location' => 'Taman Lingkungan RT 04 RW 02 Kelurahan Surodakan',
                'notes' => 'Terdapat pohon trembesi tua yang dahannya rawan patah saat hujan angin deras.',
                'status' => ServiceRequestStatus::Scheduled,
            ]
        );
        $this->attachStandardDocuments($req3, 'ktp_siti_aminah.jpg', 'surat_permohonan_rw02.pdf');
        Schedule::firstOrCreate(
            ['service_request_id' => $req3->id],
            [
                'scheduled_date' => $req3Date,
                'notes' => 'Tim 1 Pemangkasan DLH siap pukul 08.30 WIB dengan armada skylift dan chainsaw.',
            ]
        );
        $this->logStatusChange($req3, null, ServiceRequestStatus::Submitted, $clientSiti->id, $now->copy()->subDays(6));
        if ($officer) {
            $this->logStatusChange($req3, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(4));
            $this->logStatusChange($req3, ServiceRequestStatus::Confirmed, ServiceRequestStatus::Scheduled, $officer->id, $now->copy()->subDays(3));
        }

        // ---------------------------------------------------------------------
        // 4. Permohonan Selesai (Completed - Non Retribusi) - Perapian Median Jalan
        // ---------------------------------------------------------------------
        $req4Date = $now->copy()->subDays(10)->format('Y-m-d');
        $req4 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202608-0004'],
            [
                'applicant_id' => $clientAhmad->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $tidyingType->id,
                'letter_number' => '002/RT05/VIII/2026',
                'request_date' => $now->copy()->subDays(25)->format('Y-m-d'),
                'activity_date' => $req4Date,
                'location' => 'Median Jalan Panglima Sudirman Depan Pertokoan',
                'notes' => 'Tanaman hias median jalan terlalu tinggi menghalangi pandangan pengendara saat putar balik.',
                'status' => ServiceRequestStatus::Completed,
            ]
        );
        $this->attachStandardDocuments($req4, 'ktp_ahmad_fauzi_2.jpg', 'surat_permohonan_median.pdf');
        Schedule::firstOrCreate(
            ['service_request_id' => $req4->id],
            [
                'scheduled_date' => $req4Date,
                'notes' => 'Pekerjaan perapian dan pemotongan rumput taman median jalan telah tuntas 100%.',
            ]
        );
        $this->logStatusChange($req4, null, ServiceRequestStatus::Submitted, $clientAhmad->id, $now->copy()->subDays(25));
        if ($officer) {
            $this->logStatusChange($req4, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(20));
            $this->logStatusChange($req4, ServiceRequestStatus::Confirmed, ServiceRequestStatus::Scheduled, $officer->id, $now->copy()->subDays(18));
            $this->logStatusChange($req4, ServiceRequestStatus::Scheduled, ServiceRequestStatus::Completed, $officer->id, $now->copy()->subDays(10));
        }

        // ---------------------------------------------------------------------
        // 5. Peminjaman Lahan: Menunggu Pembayaran (AwaitingPayment)
        // ---------------------------------------------------------------------
        $req5Date = $now->copy()->addDays(12)->format('Y-m-d');
        $req5 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0005'],
            [
                'applicant_id' => $clientDewi->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $landLoanType->id,
                'letter_number' => '07/PAN-FEST/TGK/IX/2026',
                'request_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'activity_date' => $req5Date,
                'location' => 'Area Terbuka Hijau Alun-Alun Kabupaten Trenggalek',
                'notes' => 'Pameran Karya Kreatif Pemuda dan Bazar UMKM Lingkungan Hidup selama 2 hari.',
                'status' => ServiceRequestStatus::AwaitingPayment,
            ]
        );
        $this->attachStandardDocuments($req5, 'ktp_dewi_lestari.jpg', 'proposal_izin_alun_alun.pdf');
        Schedule::firstOrCreate(
            ['service_request_id' => $req5->id],
            [
                'scheduled_date' => $req5Date,
                'notes' => 'Pemasangan tenda bazar dapat dimulai H-1 sore setelah koordinasi dengan petugas RTH.',
            ]
        );
        $permit5 = Permit::firstOrCreate(
            ['service_request_id' => $req5->id],
            [
                'permit_number' => '660/012/IZIN-RTH/DLH/2026',
                'issued_date' => $now->copy()->subDay()->format('Y-m-d'),
                'valid_until' => $now->copy()->addDays(14)->format('Y-m-d'),
                'file_path' => 'permits/surat_izin_alun_alun_012.pdf',
            ]
        );
        if ($tariffDay) {
            Payment::firstOrCreate(
                ['permit_id' => $permit5->id],
                [
                    'tariff_id' => $tariffDay->id,
                    'amount' => 100000.00, // 2 hari x 50.000
                    'status' => PaymentStatus::Pending,
                    'paid_at' => null,
                    'proof_path' => null,
                ]
            );
        }
        $this->logStatusChange($req5, null, ServiceRequestStatus::Submitted, $clientDewi->id, $now->copy()->subDays(5));
        if ($officer) {
            $this->logStatusChange($req5, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(3));
            $this->logStatusChange($req5, ServiceRequestStatus::Confirmed, ServiceRequestStatus::Scheduled, $officer->id, $now->copy()->subDays(2));
            $this->logStatusChange($req5, ServiceRequestStatus::Scheduled, ServiceRequestStatus::PermitIssued, $officer->id, $now->copy()->subDay());
            $this->logStatusChange($req5, ServiceRequestStatus::PermitIssued, ServiceRequestStatus::AwaitingPayment, $officer->id, $now->copy()->subDay());
        }

        // ---------------------------------------------------------------------
        // 6. Peminjaman Lahan: Lunas (Paid)
        // ---------------------------------------------------------------------
        $req6Date = $now->copy()->addDays(15)->format('Y-m-d');
        $req6 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0006'],
            [
                'applicant_id' => $clientBudi->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $landLoanType->id,
                'letter_number' => '421/089/SMPN1/IX/2026',
                'request_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'activity_date' => $req6Date,
                'location' => 'Kawasan Hutan Kota Trenggalek',
                'notes' => 'Kegiatan Perkemahan Pramuka Gugus Depan SMPN 1 Trenggalek dan Aksi Tanam Pohon.',
                'status' => ServiceRequestStatus::Paid,
            ]
        );
        $this->attachStandardDocuments($req6, 'ktp_budi_santoso_pramuka.jpg', 'surat_permohonan_kemah.pdf');
        Schedule::firstOrCreate(
            ['service_request_id' => $req6->id],
            [
                'scheduled_date' => $req6Date,
                'notes' => 'Lokasi camping ground blok B Hutan Kota sudah disiapkan dan dibersihkan.',
            ]
        );
        $permit6 = Permit::firstOrCreate(
            ['service_request_id' => $req6->id],
            [
                'permit_number' => '660/018/IZIN-RTH/DLH/2026',
                'issued_date' => $now->copy()->subDays(4)->format('Y-m-d'),
                'valid_until' => $now->copy()->addDays(17)->format('Y-m-d'),
                'file_path' => 'permits/surat_izin_hutan_kota_018.pdf',
            ]
        );
        $tariffForReq6 = $tariffActivity ?? $tariffDay;
        if ($tariffForReq6) {
            Payment::firstOrCreate(
                ['permit_id' => $permit6->id],
                [
                    'tariff_id' => $tariffForReq6->id,
                    'amount' => 250000.00,
                    'status' => PaymentStatus::Paid,
                    'paid_at' => $now->copy()->subDays(2),
                    'proof_path' => 'payments/bukti_bayar_kemah_smpn1.jpg',
                ]
            );
        }
        $this->logStatusChange($req6, null, ServiceRequestStatus::Submitted, $clientBudi->id, $now->copy()->subDays(10));
        if ($officer) {
            $this->logStatusChange($req6, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(8));
            $this->logStatusChange($req6, ServiceRequestStatus::Confirmed, ServiceRequestStatus::Scheduled, $officer->id, $now->copy()->subDays(6));
            $this->logStatusChange($req6, ServiceRequestStatus::Scheduled, ServiceRequestStatus::PermitIssued, $officer->id, $now->copy()->subDays(4));
            $this->logStatusChange($req6, ServiceRequestStatus::PermitIssued, ServiceRequestStatus::AwaitingPayment, $officer->id, $now->copy()->subDays(4));
            $this->logStatusChange($req6, ServiceRequestStatus::AwaitingPayment, ServiceRequestStatus::Paid, $officer->id, $now->copy()->subDays(2));
        }

        // ---------------------------------------------------------------------
        // 7. Peminjaman Lahan: Selesai Penuh (Completed with Payment & Permit)
        // ---------------------------------------------------------------------
        $req7Date = $now->copy()->subDays(15)->format('Y-m-d');
        $req7 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202608-0007'],
            [
                'applicant_id' => $clientSiti->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $landLoanType->id,
                'letter_number' => '03/PKK-SRD/VIII/2026',
                'request_date' => $now->copy()->subDays(30)->format('Y-m-d'),
                'activity_date' => $req7Date,
                'location' => 'Taman Menak Sopal Trenggalek',
                'notes' => 'Kegiatan Senam Bersama dan Sosialisasi Pemilahan Sampah Rumah Tangga TP-PKK.',
                'status' => ServiceRequestStatus::Completed,
            ]
        );
        $this->attachStandardDocuments($req7, 'ktp_siti_aminah_pkk.jpg', 'surat_permohonan_pkk.pdf');
        Schedule::firstOrCreate(
            ['service_request_id' => $req7->id],
            [
                'scheduled_date' => $req7Date,
                'notes' => 'Kegiatan selesai pukul 11.00 WIB, kebersihan area taman dikembalikan seperti semula.',
            ]
        );
        $permit7 = Permit::firstOrCreate(
            ['service_request_id' => $req7->id],
            [
                'permit_number' => '660/009/IZIN-RTH/DLH/2026',
                'issued_date' => $now->copy()->subDays(24)->format('Y-m-d'),
                'valid_until' => $now->copy()->subDays(14)->format('Y-m-d'),
                'file_path' => 'permits/surat_izin_menak_sopal_009.pdf',
            ]
        );
        if ($tariffDay) {
            Payment::firstOrCreate(
                ['permit_id' => $permit7->id],
                [
                    'tariff_id' => $tariffDay->id,
                    'amount' => 50000.00,
                    'status' => PaymentStatus::Paid,
                    'paid_at' => $now->copy()->subDays(22),
                    'proof_path' => 'payments/bukti_bayar_senam_pkk.jpg',
                ]
            );
        }
        $this->logStatusChange($req7, null, ServiceRequestStatus::Submitted, $clientSiti->id, $now->copy()->subDays(30));
        if ($officer) {
            $this->logStatusChange($req7, ServiceRequestStatus::Submitted, ServiceRequestStatus::Confirmed, $officer->id, $now->copy()->subDays(26));
            $this->logStatusChange($req7, ServiceRequestStatus::Confirmed, ServiceRequestStatus::Scheduled, $officer->id, $now->copy()->subDays(25));
            $this->logStatusChange($req7, ServiceRequestStatus::Scheduled, ServiceRequestStatus::PermitIssued, $officer->id, $now->copy()->subDays(24));
            $this->logStatusChange($req7, ServiceRequestStatus::PermitIssued, ServiceRequestStatus::AwaitingPayment, $officer->id, $now->copy()->subDays(24));
            $this->logStatusChange($req7, ServiceRequestStatus::AwaitingPayment, ServiceRequestStatus::Paid, $officer->id, $now->copy()->subDays(22));
            $this->logStatusChange($req7, ServiceRequestStatus::Paid, ServiceRequestStatus::Completed, $officer->id, $now->copy()->subDays(15));
        }

        // ---------------------------------------------------------------------
        // 8. Permohonan Ditolak (Rejected)
        // ---------------------------------------------------------------------
        $req8 = ServiceRequest::firstOrCreate(
            ['request_number' => 'REQ-202609-0008'],
            [
                'applicant_id' => $clientAhmad->id,
                'officer_id' => $officer?->id,
                'service_type_id' => $trimmingType->id,
                'letter_number' => '015/RT05/IX/2026',
                'request_date' => $now->copy()->subDays(8)->format('Y-m-d'),
                'activity_date' => $now->copy()->addDays(6)->format('Y-m-d'),
                'location' => 'Pekarangan Dalam Rumah Pribadi RT 05 RW 02 Kel. Kelutan',
                'notes' => 'Pemangkasan pohon mangga di belakang rumah.',
                'status' => ServiceRequestStatus::Rejected,
            ]
        );
        $this->attachStandardDocuments($req8, 'ktp_ahmad_fauzi_reject.jpg', 'surat_pengantar_reject.pdf');
        $this->logStatusChange($req8, null, ServiceRequestStatus::Submitted, $clientAhmad->id, $now->copy()->subDays(8));
        if ($officer) {
            $this->logStatusChange(
                $req8,
                ServiceRequestStatus::Submitted,
                ServiceRequestStatus::Rejected,
                $officer->id,
                $now->copy()->subDays(7)
            );
        }
    }

    /**
     * Lampirkan 2 dokumen standar (Foto KTP dan Surat Permohonan).
     */
    private function attachStandardDocuments(ServiceRequest $request, string $ktpFileName, string $letterFileName): void
    {
        Document::firstOrCreate(
            [
                'service_request_id' => $request->id,
                'type' => DocumentType::IdCard,
            ],
            [
                'file_name' => $ktpFileName,
                'file_path' => 'documents/ktp/'.$ktpFileName,
            ]
        );

        Document::firstOrCreate(
            [
                'service_request_id' => $request->id,
                'type' => DocumentType::RequestLetter,
            ],
            [
                'file_name' => $letterFileName,
                'file_path' => 'documents/letters/'.$letterFileName,
            ]
        );
    }

    /**
     * Catat riwayat log perubahan status.
     */
    private function logStatusChange(
        ServiceRequest $request,
        ?ServiceRequestStatus $oldStatus,
        ServiceRequestStatus $newStatus,
        int $changedByUserId,
        Carbon $timestamp
    ): void {
        StatusLog::firstOrCreate(
            [
                'service_request_id' => $request->id,
                'new_status' => $newStatus,
            ],
            [
                'changed_by' => $changedByUserId,
                'old_status' => $oldStatus,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );
    }
}
