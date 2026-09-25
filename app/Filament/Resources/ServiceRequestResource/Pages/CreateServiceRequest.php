<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceRequestResource;
use App\Models\StatusLog;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateServiceRequest extends CreateRecord
{
    protected static string $resource = ServiceRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = ServiceRequestStatus::Submitted->value;

        return $data;
    }

    protected function afterCreate(): void
    {
        StatusLog::create([
            'service_request_id' => $this->record->id,
            'changed_by' => Auth::id(),
            'old_status' => null,
            'new_status' => ServiceRequestStatus::Submitted->value,
        ]);
    }
}
