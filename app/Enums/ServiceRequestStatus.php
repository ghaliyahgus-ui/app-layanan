<?php

namespace App\Enums;

enum ServiceRequestStatus: string
{
    case Submitted = 'submitted';
    case Confirmed = 'confirmed';
    case Scheduled = 'scheduled';
    case PermitIssued = 'permit_issued';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Diajukan',
            self::Confirmed => 'Dikonfirmasi',
            self::Scheduled => 'Dijadwalkan',
            self::PermitIssued => 'Surat Izin Terbit',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::Paid => 'Lunas',
            self::Completed => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'gray',
            self::Confirmed => 'info',
            self::Scheduled => 'warning',
            self::PermitIssued => 'primary',
            self::AwaitingPayment => 'warning',
            self::Paid => 'success',
            self::Completed => 'success',
            self::Rejected => 'danger',
        };
    }
}
