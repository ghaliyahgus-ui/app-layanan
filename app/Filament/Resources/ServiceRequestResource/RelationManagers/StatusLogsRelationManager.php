<?php

namespace App\Filament\Resources\ServiceRequestResource\RelationManagers;

use App\Enums\ServiceRequestStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';

    protected static ?string $title = 'Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('old_status')
                    ->label('Status Lama')
                    ->formatStateUsing(fn (?ServiceRequestStatus $state): string => $state?->label() ?? '-')
                    ->badge()
                    ->color(fn (?ServiceRequestStatus $state): string => $state?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('new_status')
                    ->label('Status Baru')
                    ->formatStateUsing(fn (ServiceRequestStatus $state): string => $state->label())
                    ->badge()
                    ->color(fn (ServiceRequestStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Diubah oleh'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
