<?php

namespace App\Filament\Resources;

use App\Enums\TariffUnit;
use App\Filament\Resources\TariffResource\Pages;
use App\Models\Tariff;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Tarif';

    protected static ?string $modelLabel = 'Tarif';

    protected static ?string $pluralModelLabel = 'Tarif';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('service_type_id')
                    ->label('Jenis Layanan')
                    ->relationship('serviceType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah Tarif (Rp)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Forms\Components\Select::make('unit')
                    ->label('Satuan')
                    ->options(collect(TariffUnit::cases())->mapWithKeys(
                        fn (TariffUnit $unit) => [$unit->value => $unit->label()]
                    ))
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Jenis Layanan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Tarif')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->formatStateUsing(fn (TariffUnit $state): string => $state->label())
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTariffs::route('/'),
            'create' => Pages\CreateTariff::route('/create'),
            'edit' => Pages\EditTariff::route('/{record}/edit'),
        ];
    }
}
