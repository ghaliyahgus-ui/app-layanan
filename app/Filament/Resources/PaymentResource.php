<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Layanan';

    protected static ?string $navigationLabel = 'Pembayaran';

    protected static ?string $modelLabel = 'Pembayaran';

    protected static ?string $pluralModelLabel = 'Pembayaran';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detail Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('permit_id')
                            ->label('No. Surat Izin')
                            ->relationship('permit', 'permit_number')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('tariff_id')
                            ->label('Tarif')
                            ->relationship('tariff', 'amount')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Bayar (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(collect(PaymentStatus::cases())->mapWithKeys(
                                fn (PaymentStatus $status) => [$status->value => $status->label()]
                            ))
                            ->required(),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Tanggal Bayar')
                            ->visible(fn ($get): bool => $get('status') === PaymentStatus::Paid->value),
                        Forms\Components\FileUpload::make('proof_path')
                            ->label('Bukti Pembayaran')
                            ->directory('payment-proofs')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('permit.permit_number')
                    ->label('No. Surat Izin')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('permit.serviceRequest.applicant.name')
                    ->label('Pemohon')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Tgl. Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum dibayar'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(PaymentStatus::cases())->mapWithKeys(
                        fn (PaymentStatus $status) => [$status->value => $status->label()]
                    )),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
