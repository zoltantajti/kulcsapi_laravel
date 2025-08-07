<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Filament\Resources\LicenseResource\RelationManagers;
use App\Models\License;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('license_key')
                    ->label('Licenszkulcs')
                    ->default(fn () => 'KEY-' . strtoupper(Str::random(12)))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Állapot')
                    ->options([
                        'available' => 'Elérhető',
                        'in_use' => 'Használatban',
                        'expired' => 'Lejárt',
                        'revoked' => 'Visszavont'
                    ]),
                Forms\Components\TextInput::make('max_activations')
                    ->label('Max. aktivációk')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Lejárati dátum')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_key')
                    ->label('Kulcs')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn (string $state): string => match($state){
                        'available' => 'success',
                        'in_use' => 'primary',
                        'expired' => 'danger',
                        'revoked' => 'gray'
                    }),
                Tables\Columns\TextColumn::make('activations_count')
                    ->label('Aktivációk'),
                Tables\Columns\TextColumn::make('max_activations')
                    ->label('Limit'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Lejárat')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}
