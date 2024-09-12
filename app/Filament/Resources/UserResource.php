<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SebastianBergmann\Type\TrueType;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Masters';
    protected static ?string $navigationLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn($livewire) => $livewire instanceof Pages\CreateUser) // Required only on creation
                    ->dehydrateStateUsing(fn($state) => bcrypt($state)) // Encrypt password
                    ->hiddenOn('edit'), // Hide on edit if you don’t want to display it
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name') // For role selection (Filament Shield)
                    ->multiple()
                    ->preload()
                    ->required(),

                // MultiSelect for assigning branches
                Forms\Components\Select::make('branches')
                    ->label('Assigned Branches')
                    ->relationship('branches', 'name') // Specify the relationship method
                    ->placeholder('Select branches')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->required(false),

                // MultiSelect for assigning regions
                Forms\Components\MultiSelect::make('regions')
                    ->label('Assigned Regions')
                    ->relationship('regions', 'name') // Specify the relationship method
                    ->placeholder('Select regions')
                    ->searchable()
                    ->preload()
                    ->required(false),

                // MultiSelect for assigning countries
                Forms\Components\MultiSelect::make('countries')
                    ->label('Assigned Countries')
                    ->relationship('countries', 'name') // Specify the relationship method
                    ->placeholder('Select countries')
                    ->searchable()
                    ->preload()
                    ->required(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
