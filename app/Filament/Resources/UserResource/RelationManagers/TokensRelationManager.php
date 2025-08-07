<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';
    protected static ?string $title = "API tokenek";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Token neve')
                    ->helperText('pl.: "Az én szoftverem, vagy fejlesztői kulcs"')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Név'),
                Tables\Columns\TextColumn::make('created_at')->label('Létrehozva')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Új token létrehozása')
                    ->using(function (array $data, RelationManager $livewire): Model {
                        $user = $livewire->getOwnerRecord();
                        $tokenName = $data['name'];
                        $token = $user->createToken($tokenName);
                        session()->flash('token', $token->plainTextToken);
                        return $token->accessToken;
                    })
                    ->mutateFormDataUsing(function (array $data): array { return $data; })
                    ->after(function ($record) {
                        $token = $record->plainTextToken;
                        session()->flash('token', $token);
                    })
                    ->successRedirectUrl(fn ($record) => url()->previous()),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        if(session()->has('token')){
            return view('filament.token-display', ['token' => session('token')]);
        };
        return null;
    }
}
