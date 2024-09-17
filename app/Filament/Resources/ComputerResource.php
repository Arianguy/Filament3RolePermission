<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\Auth;
use App\Models\Cpu;
use App\Models\Ram;
use App\Models\Vpn;
use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Computer;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ComputerModel;
use App\Models\OperatingSystem;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ComputerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ComputerResource\RelationManagers;
use App\Models\Branch; // Ensure you include this use statement

class ComputerResource extends Resource
{
    protected static ?string $model = Computer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0'); // No authenticated user
        }

        // Retrieve IDs of branches, regions, and countries associated with the user
        $branchIds = $user->branches()->pluck('branches.id')->toArray();
        $regionIds = $user->regions()->pluck('regions.id')->toArray();
        $countryIds = $user->countries()->pluck('countries.id')->toArray();

        // Start building the query
        $query = parent::getEloquentQuery();

        // Apply the filtering logic
        return $query->where(function ($query) use ($branchIds, $regionIds, $countryIds) {
            if (!empty($branchIds)) {
                $query->orWhereIn('branch_id', $branchIds);
            }

            if (!empty($regionIds)) {
                $query->orWhereHas('branch', function ($q) use ($regionIds) {
                    $q->whereIn('region_id', $regionIds);
                });
            }

            if (!empty($countryIds)) {
                $query->orWhereHas('branch', function ($q) use ($countryIds) {
                    $q->whereIn('country_id', $countryIds);
                });
            }
        });
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('branch_id')
                    ->label('Branch')
                    ->options(function () {
                        $user = Auth::user();

                        // Retrieve IDs of branches, regions, and countries associated with the user
                        $branchIds = $user->branches()->pluck('branches.id')->toArray();
                        $regionIds = $user->regions()->pluck('regions.id')->toArray();
                        $countryIds = $user->countries()->pluck('countries.id')->toArray();

                        // Fetch branches the user has access to
                        $branches = Branch::query()
                            ->where(function ($query) use ($branchIds, $regionIds, $countryIds) {
                                if (!empty($branchIds)) {
                                    $query->orWhereIn('id', $branchIds);
                                }

                                if (!empty($regionIds)) {
                                    $query->orWhereIn('region_id', $regionIds);
                                }

                                if (!empty($countryIds)) {
                                    $query->orWhereIn('country_id', $countryIds);
                                }
                            })
                            ->pluck('name', 'id');

                        return $branches;
                    })
                    ->searchable()
                    ->required()
                    ->default(function () {
                        $user = Auth::user();
                        $branchIds = $user->branches()->pluck('branches.id')->toArray();

                        // If the user has only one branch, set it as default
                        if (count($branchIds) === 1) {
                            return $branchIds[0];
                        }

                        return null;
                    }),


                TextInput::make('pc_code')->required(),
                TextInput::make('name')->required(),
                TextInput::make('imei')->required(),
                TextInput::make('cost')->numeric()->required(),
                DatePicker::make('purchase_date')->required(),
                Toggle::make('byod')->label('BYOD')->default(false),
                Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('website')->url()->nullable(),
                    ]),

                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('model_id')
                    ->label('Model')
                    ->options(ComputerModel::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('cpu_id')
                    ->label('CPU')
                    ->relationship('cpu', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('core')->required(),
                        TextInput::make('speed')->required(),
                        TextInput::make('company')->required(),
                    ]),

                Select::make('ram_id')
                    ->label('RAM')
                    ->options(function () {
                        return \App\Models\RAM::all()->mapWithKeys(function ($ram) {
                            return [$ram->id => "{$ram->capacity} GB - {$ram->speed} MHz"];
                        })->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->preload(),


                Select::make('os_id')
                    ->label('Operating System')
                    ->options(OperatingSystem::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('vpn_id')
                    ->label('VPN')
                    ->options(Vpn::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Disks Repeater
                Forms\Components\Repeater::make('disks')
                    ->label('Disks')
                    ->relationship('disks')
                    ->schema([
                        TextInput::make('disk_name')
                            ->label('Disk Name')
                            ->required(),
                        TextInput::make('capacity')
                            ->label('Capacity')
                            ->required(),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'HDD' => 'HDD',
                                'SSD' => 'SSD',
                                'NVMe SSD' => 'NVMe SSD',
                                'SATA SSD' => 'SATA SSD',
                            ])
                            ->required(),
                        TextInput::make('interface')
                            ->label('Interface')
                            ->nullable(),
                    ])
                    ->collapsible()
                    ->createItemButtonLabel('Add Disk')
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Define your table columns here
                Tables\Columns\TextColumn::make('name')->label('Computer Name'),
                Tables\Columns\TextColumn::make('brand.name')->label('Brand'),
                Tables\Columns\TextColumn::make('model.name')->label('Model'),
                Tables\Columns\TextColumn::make('cpu.name')->label('CPU'),
                Tables\Columns\TextColumn::make('ram.capacity')->label('RAM'),
                Tables\Columns\TextColumn::make('disks')
                    ->label('Disks')
                    ->formatStateUsing(function ($record) {
                        return $record->disks->pluck('disk_name')->join(', ');
                    }),
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
            'index' => Pages\ListComputers::route('/'),
            'create' => Pages\CreateComputer::route('/create'),
            'edit' => Pages\EditComputer::route('/{record}/edit'),
        ];
    }
}
