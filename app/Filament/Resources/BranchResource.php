<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Country;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BranchResource\Pages;


class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->relationship('region', 'name')
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
                    ->relationship('country', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

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
            ->query(function (Builder $query) {
                return static::applyUserRegionBranchFilter($query);
            })
            ->filters([
                // Define filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected static function applyUserRegionBranchFilter(Builder $query): Builder
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // No authenticated user
        }

        // Ensure the query builder is using the correct model
        if (!$query->getModel()) {
            // Reset the query to use the correct model
            $query = Branch::query();
        }

        // Retrieve IDs of branches, regions, and countries associated with the user
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        $regionIds = $user->regions()->pluck('regions.id')->toArray();
        $countryIds = $user->countries()->pluck('countries.id')->toArray();

        // Apply the filtering logic
        return $query->where(function ($query) use ($branchIds, $regionIds, $countryIds) {
            if (!empty($branchIds)) {
                $query->orWhereIn('id', $branchIds);
            }

            if (!empty($regionIds)) {
                $query->orWhereIn('region_id', $regionIds);
            }

            if (!empty($countryIds)) {
                $query->orWhereIn('country_id', $countryIds);
            }
        });
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
