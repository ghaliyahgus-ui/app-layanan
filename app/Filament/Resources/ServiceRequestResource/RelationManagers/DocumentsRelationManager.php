<?php

namespace App\Filament\Resources\ServiceRequestResource\RelationManagers;

use App\Enums\DocumentType;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Dokumen Pendukung';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Jenis Dokumen')
                    ->options(collect(DocumentType::cases())->mapWithKeys(
                        fn (DocumentType $type) => [$type->value => $type->label()]
                    ))
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->directory('documents')
                    ->required()
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->maxSize(5120),
                Forms\Components\TextInput::make('file_name')
                    ->label('Nama File')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (DocumentType $state): string => $state->label())
                    ->badge(),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diunggah')
                    ->dateTime('d M Y H:i'),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Unggah Dokumen'),
            ])
            ->actions([
                Actions\DeleteAction::make(),
            ]);
    }
}
