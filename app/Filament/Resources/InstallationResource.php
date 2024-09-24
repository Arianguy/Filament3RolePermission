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
                // Installation Details Section
                Forms\Components\Section::make('Installation Details')
                    ->description('Assign software licenses to computers and track their details.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('computer_id')
                                    ->label('Computer')
                                    ->relationship('computer', 'pc_code')
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Select a Computer')
                                    ->hint('Select the computer to which the software license will be assigned.')
                                    ->helperText('Search for the computer by its PC code.'),

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
                                    ->searchable()
                                    ->placeholder('Select a Software License')
                                    ->hint('Choose the appropriate software license for the selected computer.')
                                    ->helperText('Search by software name and license type.'),
                            ]),
                    ])
                    ->collapsed(false),  // Expand the section by default for visibility

                // User Credentials Section
                Forms\Components\Section::make('User Credentials')
                    ->description('Details for the user who will be using the software.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('userid')
                                    ->label('User ID')
                                    ->nullable()
                                    ->placeholder('Enter User ID')
                                    ->hint('Provide the User ID associated with this software.')
                                    ->helperText('This is the ID for the user who will utilize the software license.'),

                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()  // Mask the password field
                                    ->nullable()
                                    ->placeholder('Enter Password')
                                    ->hint('Password associated with the software account, if applicable.')
                                    ->helperText('Only enter this if the software requires credentials.'),
                            ]),
                    ])
                    ->collapsed(),  // Initially collapsed for a cleaner view

                // License Details Section
                Forms\Components\Section::make('License Details')
                    ->description('Information about the license key and assignment details.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('License Key')
                                    ->nullable()
                                    ->placeholder('Enter License Key')
                                    ->hint('Provide the software license key.')
                                    ->helperText('This is the unique key for activating the software.'),

                                Forms\Components\DatePicker::make('assigned_at')
                                    ->label('Assigned At')
                                    ->nullable()
                                    ->placeholder('Select Assignment Date')
                                    ->hint('Specify the date when the software was assigned to the user.')
                                    ->helperText('Enter the date this license was assigned to the computer.'),
                            ]),
                    ])
                    ->collapsed(),  // Initially collapsed for simplicity
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
