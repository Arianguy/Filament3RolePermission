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
                    ->description('Assign software licenses to computers.')
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
                                    ->hint('Choose the appropriate software license for the selected computer.'),
                            ]),
                    ])
                    ->collapsed(false),  // Expand the section by default for visibility

                // User Credentials Section
                Forms\Components\Section::make('License Details')
                    ->description('Details for the user who will be using the software.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('userid')
                                    ->label('User ID')
                                    ->nullable()
                                    ->placeholder('Enter User ID')
                                    ->hint('Provide the User ID associated with this software, if applicable.'),
                                //->helperText('This is the ID for the user who will utilize the software license.'),

                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()  // Mask the password field
                                    ->nullable()
                                    ->placeholder('Enter Password')
                                    ->hint('Password associated with the software account, if applicable.'),
                                // ->helperText('Only enter this if the software requires credentials.'),

                                Forms\Components\TextInput::make('key')
                                    ->label('License Key')
                                    ->nullable()
                                    ->placeholder('Enter License Key')
                                    ->hint('Provide the software license key.'),
                            ]),
                    ])
                    ->collapsed(false),  // Initially collapsed for a cleaner view

                // License Details Section



                Forms\Components\DatePicker::make('assigned_at')
                    ->label('Assigned At')
                    ->required()
                    ->hint('Specify the date when the software was assigned to the computer.'),
                // Initially collapsed for simplicity
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
                Tables\Filters\Filter::make('branch_id')
                    ->form([
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->options(function () {
                                return \App\Models\Branch::pluck('name', 'id');  // Fetch branches from Branch model
                            })
                            ->searchable()
                            ->placeholder('Select a Branch'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['branch_id'],
                            fn($query, $branchId) =>
                            $query->whereHas('computer', fn($query) => $query->where('branch_id', $branchId))  // Accessing branch_id via computer relationship
                        );
                    })
                    ->label('Branch'),

                Tables\Filters\Filter::make('assigned_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($query) => $query->whereDate('assigned_at', '>=', $data['from']))
                            ->when($data['until'], fn($query) => $query->whereDate('assigned_at', '<=', $data['until']));
                    })
                    ->label('Assigned Date Range'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
