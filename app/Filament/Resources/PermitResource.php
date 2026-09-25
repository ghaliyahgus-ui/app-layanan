<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermitResource\Pages;
use App\Models\Permit;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PermitResource extends Resource
{
    protected static ?string $model = Permit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Surat Izin';

    protected static ?string $modelLabel = 'Surat Izin';

    protected static ?string $pluralModelLabel = 'Surat Izin';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detail Surat Izin')
                    ->schema([
                        Forms\Components\Select::make('service_request_id')
                            ->label('No. Permohonan')
                            ->relationship('serviceRequest', 'request_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('permit_number')
                            ->label('No. Surat Izin')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('issued_date')
                            ->label('Tanggal Terbit')
                            ->required(),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Berlaku Sampai'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Surat Izin')
                            ->directory('permits')
                            ->acceptedFileTypes(['application/pdf'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permit_number')
                    ->label('No. Surat Izin')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('serviceRequest.request_number')
                    ->label('No. Permohonan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serviceRequest.applicant.name')
                    ->label('Pemohon')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issued_date')
                    ->label('Tgl. Terbit')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('issued_date', 'desc')
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermits::route('/'),
            'create' => Pages\CreatePermit::route('/create'),
            'edit' => Pages\EditPermit::route('/{record}/edit'),
        ];
    }
}
