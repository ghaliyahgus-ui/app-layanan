<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceRequestResource;
use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestRequestsWidget extends BaseWidget
{
    protected static ?string $heading = '5 Permohonan Terbaru';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ServiceRequest::query()->with(['applicant', 'serviceType'])->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Permohonan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('applicant.name')
                    ->label('Pemohon'),
                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Jenis Layanan')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ServiceRequestStatus $state): string => $state->label())
                    ->color(fn (ServiceRequestStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('Tgl. Pengajuan')
                    ->date('d M Y'),
            ])
            ->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ServiceRequest $record): string => ServiceRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
