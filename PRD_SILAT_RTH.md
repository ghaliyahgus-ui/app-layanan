# PRD — Sistem Informasi Layanan Dinas Lingkungan Hidup

**SILAT RTH (Sistem Layanan Cepat Ruang Terbuka Hijau)**

> 💡 **Tips:** PRD (Product Requirements Document) itu cuma "surat permintaan" ke AI coding assistant (misalnya Claude). Isi dengan bahasa sehari-hari, tidak perlu istilah teknis. Semakin jelas dan konkret isiannya, semakin bagus hasil sistem yang dibuatkan AI.

| | |
| --- | --- |
| **Nama Sistem** | Sistem Informasi Layanan Dinas Lingkungan Hidup (SILAT RTH) |
| **Tanggal** | 23 September 2026 |
| **Disusun oleh** | Ghaliyah Ayu Guswami — Pengendali Dampak Lingkungan Ahli Pertama |
| **Instansi** | Dinas Lingkungan Hidup Kabupaten Trenggalek |

---

## 1. Ringkasan Singkat

Dinas Lingkungan Hidup butuh sistem online untuk layanan pengelolaan Ruang Terbuka Hijau (RTH) dan sewa/peminjaman fasilitas RTH. Klien (masyarakat atau instansi lain) bisa mendaftar dan mengajukan permohonan secara online, staf tidak perlu lagi mencatat manual, dan pimpinan bisa melihat laporan kapan saja.

**Target:** mempersingkat waktu layanan pendaftaran.

---

## 2. Siapa Saja yang Akan Memakai Sistem

| Peran | Contoh Orangnya | Bisa Ngapain Saja |
| --- | --- | --- |
| **Admin** | Staf Bidang Tata Lingkungan, Pengelolaan Keanekaragaman Hayati dan Peningkatan Kapasitas | Atur semua data & akses pengguna lain |
| **Petugas Layanan** | Staf Bidang Tata Lingkungan, Pengelolaan Keanekaragaman Hayati dan Peningkatan Kapasitas | Input pendaftaran, konfirmasi, penjadwalan, terbitkan surat izin, cetak bukti/invoice pembayaran |
| **Pimpinan** | Kepala Dinas Lingkungan Hidup Kabupaten Trenggalek | Lihat dashboard & laporan (tidak bisa ubah data) |
| **Klien** | Masyarakat / instansi lain | Daftar layanan, upload dokumen, cek status |

---

## 3. Layanan yang Ingin Dibuat Sistemnya

### Layanan A — SILAT RTH (Sistem Layanan Cepat Ruang Terbuka Hijau)

Layanan ini mencakup tiga jenis permohonan:

1. Pemangkasan
2. Perapian
3. Peminjaman lahan

**a) Apa langkah-langkahnya, dari klien datang sampai selesai?**

1. Klien mengajukan permohonan pelayanan (pemangkasan / perapian / peminjaman lahan).
2. Klien mengisi formulir dan mengunggah surat permohonan.
3. Petugas mengecek usulan permohonan dan mengonfirmasi kepada pemohon.
4. Petugas menjadwalkan pelayanan yang telah diajukan.
5. Khusus peminjaman lahan: diterbitkan surat izin.
6. Pemohon membayar tarif sesuai yang tertera di surat izin.

**b) Data apa saja yang perlu disimpan sistem?**

- Nama
- Tanggal kegiatan
- Nomor telepon
- Alamat
- Foto KTP
- Nomor surat pengajuan dari pemohon

**c) Ada aturan khusus yang harus dipatuhi sistem?**

- Pengajuan layanan dilakukan **H-7 sebelum kegiatan** (sistem menolak pengajuan yang tanggal kegiatannya kurang dari 7 hari dari tanggal pengajuan).

---

## 4. Laporan & Dashboard yang Dibutuhkan

| Ingin lihat apa? | Contoh |
| --- | --- |
| **Info di dashboard utama** | Jumlah pemohon, jumlah pemohon yang telah ditindaklanjuti |
| **Laporan rutin** | Rekap bulanan (Excel/PDF), statistik pemohon per bulan |

---

## 5. Catatan untuk AI Coding Assistant

*Bagian ini boleh dibiarkan seperti apa adanya. Ini "instruksi teknis" otomatis untuk AI.*

- Tiap Layanan di Bagian 3 → jadi satu modul/menu di sistem
- Tiap langkah alur → jadi status transaksi (contoh: Menunggu → Dikonfirmasi → Lunas → Selesai)
- Tiap data yang dicatat → jadi kolom di database
- Tiap aturan bisnis → jadi validasi otomatis di sistem
- Bagian 4 → jadi tampilan dashboard + tombol ekspor PDF/Excel

### 5.1 Alur status permohonan (turunan dari Bagian 3a)

```mermaid
flowchart LR
    A[Diajukan] --> B[Dikonfirmasi]
    B --> C[Dijadwalkan]
    C -->|Pemangkasan / Perapian| E[Selesai]
    C -->|Peminjaman lahan| D[Surat Izin Terbit]
    D --> F[Menunggu Pembayaran]
    F --> G[Lunas]
    G --> E
    A -->|Ditolak petugas| X[Ditolak]
    B -->|Ditolak petugas| X
```

### 5.2 Validasi otomatis

- Tanggal kegiatan minimal 7 hari setelah tanggal pengajuan (H-7).
- Formulir tidak bisa dikirim jika foto KTP atau surat permohonan belum diunggah.
- Surat izin dan pembayaran hanya muncul untuk jenis layanan **Peminjaman lahan**.
- Peran **Pimpinan** hanya bisa membaca (tanpa tombol ubah/hapus).

### 5.3 ER Diagram (standar Laravel)

Konvensi yang dipakai: nama tabel **jamak, snake_case, bahasa Inggris**; nama kolom snake_case bahasa Inggris; primary key `id`; foreign key berformat `<nama_tabel_tunggal>_id`; kolom `created_at` dan `updated_at` di setiap tabel (dibuat otomatis lewat `$table->timestamps()`).

```mermaid
erDiagram
    users ||--o{ service_requests : "applicant_id"
    users ||--o{ service_requests : "officer_id"
    service_types ||--o{ service_requests : "service_type_id"
    service_types ||--o{ tariffs : "service_type_id"
    service_requests ||--o{ documents : "service_request_id"
    service_requests ||--o| schedules : "service_request_id"
    service_requests ||--o| permits : "service_request_id"
    permits ||--o| payments : "permit_id"
    tariffs ||--o{ payments : "tariff_id"
    service_requests ||--o{ status_logs : "service_request_id"
    users ||--o{ status_logs : "changed_by"

    users {
        bigint id PK
        string name
        string email UK
        string password
        string phone
        text address
        string role "admin, officer, leader, client"
        timestamp email_verified_at "nullable"
        string remember_token "nullable"
        timestamp created_at
        timestamp updated_at
    }

    service_types {
        bigint id PK
        string name "trimming, tidying, land_loan"
        boolean requires_permit
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    service_requests {
        bigint id PK
        string request_number UK
        bigint applicant_id FK
        bigint officer_id FK "nullable"
        bigint service_type_id FK
        string letter_number
        date request_date
        date activity_date "min. 7 days after request_date"
        string location
        text notes "nullable"
        string status "submitted, confirmed, scheduled, permit_issued, awaiting_payment, paid, completed, rejected"
        timestamp created_at
        timestamp updated_at
    }

    documents {
        bigint id PK
        bigint service_request_id FK
        string type "id_card, request_letter"
        string file_name
        string file_path
        timestamp created_at
        timestamp updated_at
    }

    schedules {
        bigint id PK
        bigint service_request_id FK
        date scheduled_date
        text notes "nullable"
        timestamp created_at
        timestamp updated_at
    }

    permits {
        bigint id PK
        bigint service_request_id FK
        string permit_number UK
        date issued_date
        date valid_until "nullable"
        string file_path
        timestamp created_at
        timestamp updated_at
    }

    tariffs {
        bigint id PK
        bigint service_type_id FK
        decimal amount
        string unit "per_day, per_activity"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    payments {
        bigint id PK
        bigint permit_id FK
        bigint tariff_id FK
        decimal amount
        string status "pending, paid"
        timestamp paid_at "nullable"
        string proof_path "nullable"
        timestamp created_at
        timestamp updated_at
    }

    status_logs {
        bigint id PK
        bigint service_request_id FK
        bigint changed_by FK "users.id"
        string old_status "nullable"
        string new_status
        timestamp created_at
        timestamp updated_at
    }
```

**Padanan istilah Indonesia → tabel/kolom database**

| Istilah di PRD | Tabel / Kolom |
| --- | --- |
| Pengguna / peran | `users` / `users.role` |
| Jenis layanan (pemangkasan, perapian, peminjaman lahan) | `service_types` |
| Permohonan | `service_requests` |
| Nama, nomor telepon, alamat | `users.name`, `users.phone`, `users.address` |
| Tanggal kegiatan | `service_requests.activity_date` |
| Nomor surat pengajuan | `service_requests.letter_number` |
| Foto KTP, surat permohonan | `documents` (`type` = `id_card` / `request_letter`) |
| Penjadwalan | `schedules` |
| Surat izin | `permits` |
| Tarif dan pembayaran | `tariffs`, `payments` |
| Riwayat status | `status_logs` |

### 5.4 Kebutuhan dashboard & ekspor

- **Dashboard:** jumlah pemohon (total), jumlah pemohon yang sudah ditindaklanjuti (`status` selain `submitted`).
- **Laporan bulanan:** rekap permohonan per bulan dan statistik pemohon per bulan, dengan tombol **Ekspor PDF** dan **Ekspor Excel**.
- Data sumber laporan: tabel `service_requests` (dikelompokkan per `request_date` dan `service_type_id`) dan `payments`.

---

## Catatan Asumsi (mohon dicek dan diedit)

Hal-hal berikut saya tambahkan agar draf lengkap, tetapi belum tertulis di template Anda:

1. **Alur pemangkasan/perapian** diasumsikan tanpa surat izin dan tanpa pembayaran. Jika keduanya juga berbayar, ubah aturan di 5.2.
2. **Status "Ditolak"** ditambahkan sebagai jalur alternatif saat petugas mengecek permohonan.
3. **Tarif** (`tariffs`) disimpan di tabel terpisah agar mudah diubah tanpa mengubah kode. Besaran dan satuan tarif belum diisi.
4. **Riwayat status** (`status_logs`) ditambahkan untuk melacak siapa mengubah status dan kapan (berguna untuk laporan "ditindaklanjuti").
5. Pembayaran diasumsikan dicatat petugas setelah klien membayar, bukan lewat payment gateway online.
