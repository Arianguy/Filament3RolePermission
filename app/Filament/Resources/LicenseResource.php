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

class LicenseResource extends Resource
{
    protected static ?string $model = License::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('software_id')
                    ->relationship('software', 'name')
                    ->required(),
                //Forms\Components\TextInput::make('license_type')->required(),
                // Forms\Components\TextInput::make('name')->nullable(),
                Forms\Components\Select::make('license_type')
                    ->options([
                        'Open Source' => 'Open Source',
                        'Subsription' => 'Subsription',
                        'Perpectual' => 'Perpectual',
                    ])
                    ->label('License Type'),
                Forms\Components\Select::make('category')
                    ->options([
                        'office' => 'Office Software',
                        'antivirus' => 'Antivirus',
                        'developer_tools' => 'Developer Tools',
                        'os' => 'Operating System',
                    ])
                    ->label('Category'),
                Forms\Components\TextInput::make('seats_available')->numeric()->nullable(),
                //   Forms\Components\TextInput::make('seats_used')->numeric()->default(0),
                Forms\Components\DatePicker::make('valid_from')->nullable(),
                Forms\Components\DatePicker::make('valid_to')->nullable(),
                //   Forms\Components\TextInput::make('license_key')->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('software.name'),
                Tables\Columns\TextColumn::make('license_type'),
                // Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('seats_available'),
                Tables\Columns\TextColumn::make('seats_used'),
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
