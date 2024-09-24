<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallationResource\Pages;
use App\Filament\Resources\InstallationResource\RelationManagers;
use App\Models\Installation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstallationResource extends Resource
{
    protected static ?string $model = Installation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('computer_id')
                    ->label('Computer')
                    ->relationship('computer', 'pc_code')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('license_id')
                    ->label('Software License')
                    ->options(function () {
                        return \App\Models\License::with('software')
                            ->get()
                            ->mapWithKeys(function ($license) {
                                return [
                                    $license->id => optional($license->software)->name . ' - ' . $license->license_type,
                                ];
                            });
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('userid')
                    ->label('User ID')
                    ->nullable(),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    // ->password()
                    ->nullable(),
                Forms\Components\TextInput::make('license_key')
                    ->label('License Key')
                    ->nullable(),
                Forms\Components\DatePicker::make('assigned_at')
                    ->label('Assigned At')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('computer.pc_code')
                    ->label('Computer')
                    ->sortable()  // Enable sorting if needed
                    ->searchable(),  // Enable search functionality if necessary
                Tables\Columns\TextColumn::make('license.software.name')
                    ->label('Software')
                    ->sortable()
                    ->searchable(),  // Enable search functionality if necessary
                Tables\Columns\TextColumn::make('userid')
                    ->label('User ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label('License Key')
                    ->sortable()
                    ->searchable(),  // Enable search functionality
                Tables\Columns\TextColumn::make('assigned_at')
                    ->label('Assigned At')
                    ->date()
                    ->sortable(),
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
            'index' => Pages\ListInstallations::route('/'),
            'create' => Pages\CreateInstallation::route('/create'),
            'edit' => Pages\EditInstallation::route('/{record}/edit'),
        ];
    }
}
