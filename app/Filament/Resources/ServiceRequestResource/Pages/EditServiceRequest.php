<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Enums\PaymentStatus;
use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceRequestResource;
use App\Models\Permit;
use App\Models\Schedule;
use App\Models\StatusLog;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditServiceRequest extends EditRecord
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Konfirmasi: submitted → confirmed
            Actions\Action::make('confirm')
                ->label('Konfirmasi')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn () => $this->record->status === ServiceRequestStatus::Submitted)
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Permohonan')
                ->modalDescription('Apakah Anda yakin ingin mengonfirmasi permohonan ini?')
                ->action(function () {
                    $this->transitionStatus(ServiceRequestStatus::Confirmed, [
                        'officer_id' => Auth::id(),
                    ]);
                    Notification::make()->title('Permohonan dikonfirmasi')->success()->send();
                }),

            // Jadwalkan: confirmed → scheduled
            Actions\Action::make('schedule')
                ->label('Jadwalkan')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->visible(fn () => $this->record->status === ServiceRequestStatus::Confirmed)
                ->schema([
                    Forms\Components\DatePicker::make('scheduled_date')
                        ->label('Tanggal Pelaksanaan')
                        ->required()
                        ->afterOrEqual(today()),
                    Forms\Components\Textarea::make('schedule_notes')
                        ->label('Catatan Jadwal'),
                ])
                ->action(function (array $data) {
                    Schedule::create([
                        'service_request_id' => $this->record->id,
                        'scheduled_date' => $data['scheduled_date'],
                        'notes' => $data['schedule_notes'] ?? null,
                    ]);
                    $this->transitionStatus(ServiceRequestStatus::Scheduled);
                    Notification::make()->title('Permohonan dijadwalkan')->success()->send();
                }),

            // Terbitkan Surat Izin: scheduled → permit_issued (hanya peminjaman lahan)
            Actions\Action::make('issuePermit')
                ->label('Terbitkan Surat Izin')
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->visible(fn () => $this->record->status === ServiceRequestStatus::Scheduled
                    && $this->record->serviceType?->requires_permit)
                ->schema([
                    Forms\Components\TextInput::make('permit_number')
                        ->label('No. Surat Izin')
                        ->required(),
                    Forms\Components\DatePicker::make('issued_date')
                        ->label('Tanggal Terbit')
                        ->required()
                        ->default(today()),
                    Forms\Components\DatePicker::make('valid_until')
                        ->label('Berlaku Sampai'),
                    Forms\Components\FileUpload::make('permit_file')
                        ->label('File Surat Izin')
                        ->directory('permits')
                        ->acceptedFileTypes(['application/pdf']),
                ])
                ->action(function (array $data) {
                    Permit::create([
                        'service_request_id' => $this->record->id,
                        'permit_number' => $data['permit_number'],
                        'issued_date' => $data['issued_date'],
                        'valid_until' => $data['valid_until'] ?? null,
                        'file_path' => $data['permit_file'] ?? null,
                    ]);
                    $this->transitionStatus(ServiceRequestStatus::PermitIssued);
                    Notification::make()->title('Surat izin diterbitkan')->success()->send();
                }),

            // Tandai Menunggu Pembayaran: permit_issued → awaiting_payment
            Actions\Action::make('awaitPayment')
                ->label('Tunggu Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->visible(fn () => $this->record->status === ServiceRequestStatus::PermitIssued)
                ->requiresConfirmation()
                ->action(function () {
                    // Buat record payment pending
                    $permit = $this->record->permit;
                    $tariff = $this->record->serviceType->tariffs()->where('is_active', true)->first();

                    if ($permit && $tariff) {
                        $permit->payment()->create([
                            'tariff_id' => $tariff->id,
                            'amount' => $tariff->amount,
                            'status' => PaymentStatus::Pending->value,
                        ]);
                    }

                    $this->transitionStatus(ServiceRequestStatus::AwaitingPayment);
                    Notification::make()->title('Menunggu pembayaran')->success()->send();
                }),

            // Konfirmasi Lunas: awaiting_payment → paid
            Actions\Action::make('confirmPayment')
                ->label('Konfirmasi Lunas')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->status === ServiceRequestStatus::AwaitingPayment)
                ->schema([
                    Forms\Components\FileUpload::make('proof_path')
                        ->label('Bukti Pembayaran')
                        ->directory('payment-proofs')
                        ->acceptedFileTypes(['image/*', 'application/pdf']),
                ])
                ->action(function (array $data) {
                    $payment = $this->record->permit?->payment;
                    if ($payment) {
                        $payment->update([
                            'status' => PaymentStatus::Paid->value,
                            'paid_at' => now(),
                            'proof_path' => $data['proof_path'] ?? null,
                        ]);
                    }
                    $this->transitionStatus(ServiceRequestStatus::Paid);
                    Notification::make()->title('Pembayaran dikonfirmasi lunas')->success()->send();
                }),

            // Selesaikan: scheduled/paid → completed
            Actions\Action::make('complete')
                ->label('Selesaikan')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [
                    ServiceRequestStatus::Scheduled,
                    ServiceRequestStatus::Paid,
                ]) && (
                    // Non-izin: langsung dari scheduled
                    ! $this->record->serviceType?->requires_permit
                    // Izin: harus sudah paid
                    || $this->record->status === ServiceRequestStatus::Paid
                ))
                ->requiresConfirmation()
                ->modalHeading('Selesaikan Permohonan')
                ->action(function () {
                    $this->transitionStatus(ServiceRequestStatus::Completed);
                    Notification::make()->title('Permohonan selesai')->success()->send();
                }),

            // Tolak: submitted/confirmed → rejected
            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, [
                    ServiceRequestStatus::Submitted,
                    ServiceRequestStatus::Confirmed,
                ]))
                ->schema([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['notes' => $data['rejection_reason']]);
                    $this->transitionStatus(ServiceRequestStatus::Rejected);
                    Notification::make()->title('Permohonan ditolak')->danger()->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Helper untuk transisi status & pencatatan log.
     */
    private function transitionStatus(ServiceRequestStatus $newStatus, array $additionalData = []): void
    {
        $oldStatus = $this->record->status;

        $this->record->update(array_merge(
            ['status' => $newStatus->value],
            $additionalData,
        ));

        StatusLog::create([
            'service_request_id' => $this->record->id,
            'changed_by' => Auth::id(),
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
        ]);

        $this->refreshFormData(['status']);
    }
}
