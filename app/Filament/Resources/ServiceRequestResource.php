<?php

namespace App\Filament\Resources;

use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Resources\ServiceRequestResource\RelationManagers;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Permohonan Layanan';

    protected static ?string $modelLabel = 'Permohonan';

    protected static ?string $pluralModelLabel = 'Permohonan Layanan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data Permohonan')
                    ->schema([
                        Forms\Components\TextInput::make('request_number')
                            ->label('No. Permohonan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('service_type_id')
                            ->label('Jenis Layanan')
                            ->relationship('serviceType', 'name')
                            ->options(ServiceType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('applicant_id')
                            ->label('Pemohon')
                            ->relationship('applicant', 'name', fn (Builder $query) => $query->where('role', UserRole::Client))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('officer_id')
                            ->label('Petugas')
                            ->relationship('officer', 'name', fn (Builder $query) => $query->whereIn('role', [UserRole::Officer, UserRole::Admin]))
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Detail Kegiatan')
                    ->schema([
                        Forms\Components\TextInput::make('letter_number')
                            ->label('No. Surat Pengajuan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('request_date')
                            ->label('Tanggal Pengajuan')
                            ->required()
                            ->default(now()),
                        Forms\Components\DatePicker::make('activity_date')
                            ->label('Tanggal Kegiatan')
                            ->required()
                            ->afterOrEqual(fn ($get): ?string => $get('request_date')
                                ? now()->parse($get('request_date'))->addDays(7)->toDateString()
                                : null)
                            ->helperText('Minimal 7 hari setelah tanggal pengajuan (H-7)'),
                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(collect(ServiceRequestStatus::cases())->mapWithKeys(
                                fn (ServiceRequestStatus $status) => [$status->value => $status->label()]
                            ))
                            ->default(ServiceRequestStatus::Submitted->value)
                            ->required()
                            ->disabled(fn (?ServiceRequest $record) => $record !== null),
                    ])
                    ->hiddenOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Permohonan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('applicant.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Jenis Layanan')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity_date')
                    ->label('Tgl. Kegiatan')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ServiceRequestStatus $state): string => $state->label())
                    ->color(fn (ServiceRequestStatus $state): string => $state->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('officer.name')
                    ->label('Petugas')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('request_date')
                    ->label('Tgl. Pengajuan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ServiceRequestStatus::cases())->mapWithKeys(
                        fn (ServiceRequestStatus $status) => [$status->value => $status->label()]
                    )),
                Tables\Filters\SelectFilter::make('service_type_id')
                    ->label('Jenis Layanan')
                    ->relationship('serviceType', 'name'),
                Tables\Filters\Filter::make('activity_date')
                    ->schema([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('activity_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('activity_date', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\StatusLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'view' => Pages\ViewServiceRequest::route('/{record}'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
