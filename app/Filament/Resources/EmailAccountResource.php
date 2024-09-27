<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\EmailAccount;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MultiSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmailAccountResource\Pages;
use App\Filament\Resources\EmailAccountResource\RelationManagers;

class EmailAccountResource extends Resource
{
    protected static ?string $model = EmailAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email_address')->email()->required(),
                Forms\Components\Select::make('status')
                    ->options(['Active' => 'Active', 'Inactive' => 'Inactive'])
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required(),
                Forms\Components\TextInput::make('main_password')->password()->required(),
                Forms\Components\TextInput::make('pc_outlook_password')->password()->required(),
                Forms\Components\TextInput::make('ios_outlook_password')->password()->required(),
                Forms\Components\TextInput::make('android_outlook_password')->password()->required(),
                Forms\Components\TextInput::make('other_password')->password()->required(),
                Forms\Components\TextInput::make('recovery_email')->email()->required(),
                Forms\Components\TextInput::make('recovery_mobile')->tel()->required(),

                Forms\Components\MultiSelect::make('computers')
                    ->relationship('computers', 'name')
                    ->label('Assigned Computers'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email_address')
                    ->label('Email Address')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return $state === 'Active' ? 'Active' : 'Inactive';
                    })
                    ->colors([
                        'primary' => 'Active',
                        'danger' => 'Inactive',
                    ]),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('main_password')
                    ->label('Main Password')
                    ->hidden(fn() => true), // Hide password fields for security
                TextColumn::make('pc_outlook_password')
                    ->label('PC Outlook Password')
                    ->hidden(fn() => true),
                TextColumn::make('ios_outlook_password')
                    ->label('iOS Outlook Password')
                    ->hidden(fn() => true),
                TextColumn::make('android_outlook_password')
                    ->label('Android Outlook Password')
                    ->hidden(fn() => true),
                TextColumn::make('other_password')
                    ->label('Other Password')
                    ->hidden(fn() => true),
                TextColumn::make('recovery_email')
                    ->label('Recovery Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('recovery_mobile')
                    ->label('Recovery Mobile')
                    ->sortable(),
                TextColumn::make('computers.name')
                    ->label('Assigned Computers')
                    ->getStateUsing(fn($record) => $record->computers->pluck('name')->join(', ')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('viewPasswords')
                    ->label('View Passwords')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Passwords for ' . $record->name)
                    ->modalWidth('sm')
                    ->modalContent(fn($record) => view('filament.password-viewer', ['record' => $record]))

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
            'index' => Pages\ListEmailAccounts::route('/'),
            'create' => Pages\CreateEmailAccount::route('/create'),
            'edit' => Pages\EditEmailAccount::route('/{record}/edit'),
        ];
    }
}
