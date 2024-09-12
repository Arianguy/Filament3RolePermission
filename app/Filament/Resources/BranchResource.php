<?php

namespace App\Filament\Resources;

use App\Models\Branch;
use App\Models\Region;
use App\Models\Country;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BranchResource\Pages;
use Illuminate\Support\Facades\Auth;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form schema for creating and editing branches.
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Branch Code')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('name')
                    ->label('Branch Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('area')
                    ->label('Area')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('region_id')
                    ->label('Region')
                    ->options(Region::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->required()
                    ->maxLength(15),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),

                Forms\Components\Select::make('country_id')
                    ->label('Country')
                    ->options(Country::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
            ]);
    }

    /**
     * Define the table columns for listing branches.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Branch Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Branch Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('area')
                    ->label('Area')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('region.name')
                    ->label('Region')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Define filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->query(function (Builder $query) {
                return static::applyUserRegionBranchFilter($query);
            })
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Apply user-specific filtering to the Branch table query.
     */
    protected static function applyUserRegionBranchFilter(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user && $user->regions()->exists()) {
            $regionIds = $user->regions()->pluck('id')->toArray();
            return $query->whereIn('region_id', $regionIds);
        }

        if ($user && $user->branches()->exists()) {
            $branchIds = $user->branches()->pluck('id')->toArray();
            return $query->whereIn('id', $branchIds);
        }

        return $query; // No filtering, return all branches
    }

    public static function getRelations(): array
    {
        return [
            // Define relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
