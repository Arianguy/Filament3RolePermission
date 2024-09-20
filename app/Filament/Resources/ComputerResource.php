<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
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
use Illuminate\Support\Str;
use App\Models\ComputerModel;
use Filament\Facades\Filament;
use Tables\Columns\TextColumn;
use App\Models\OperatingSystem;
use Filament\Resources\Resource;
use Filament\Forms\Components\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\ComputerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ComputerResource\RelationManagers;
use App\Models\Branch; // Ensure you include this use statement

class ComputerResource extends Resource
{
    protected static ?string $model = Computer::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

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

    public static function query(): Builder
    {
        return parent::query()->with('brand');
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($computer) {
    //         Log::info('Inside creating hook'); // Confirm this log appears
    //         if (empty($computer->pc_code)) {
    //             $computer->pc_code = strtoupper(Str::random(5));
    //             Log::info('Generated PC Code: ' . $computer->pc_code); // Log the generated code
    //         }
    //     });
    // }



    // protected $fillable = [
    //     'branch_id',
    //     'name',
    //     'imei',
    //     'cost',
    //     'purchase_date',
    //     'warranty',
    //     'byod',
    //     'category_id',
    //     'model_id',
    //     'supplier_id',
    //     'cpu_id',
    //     'ram_id',
    //     'os_id',
    //     'vpn_id',
    //     'disks',
    //     'pc_code' // Ensure pc_code is fillable
    // ];

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
                TextInput::make('pc_code')
                    ->label('PC Code')
                    ->required()
                    ->readOnly()
                    ->default(fn() => strtoupper(Str::random(5)))
                    ->dehydrated(true),
                TextInput::make('name')->required(),
                TextInput::make('imei')->required(),
                TextInput::make('cost')->numeric()->required(),
                DatePicker::make('purchase_date')->required(),
                TextInput::make('warranty')->label('Warranty Months')->numeric()->required(),
                Toggle::make('byod')->label('BYOD')->default(false),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('Laptop'),
                    ]),

                Select::make('model_id')
                    ->label('Model')
                    ->relationship('computerModel', 'name', function ($query) {
                        $query->with('brand');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return "{$record->brand->name}  {$record->name}";
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Model Name')
                            ->required(),
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->label('Brand Name')->required(),
                                TextInput::make('website')->url()->nullable(),
                            ]),
                    ]),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    //->options(Supplier::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('phone')->required(),
                        TextInput::make('email')->required(),
                        TextInput::make('contact_person')->required(),
                    ]),
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
                        TextInput::make('gen')->required(),
                        TextInput::make('company')->required(),
                    ]),
                Select::make('ram_id')
                    ->label('RAM')
                    ->relationship('ram', 'id')
                    ->getOptionLabelFromRecordUsing(function (RAM $ram) {
                        return "{$ram->capacity} - {$ram->speed} MHz";
                    })
                    ->searchable()
                    ->required()
                    ->getSearchResultsUsing(function (string $searchQuery) {
                        return RAM::query()
                            ->where('capacity', 'like', "%{$searchQuery}%")
                            ->orWhere('speed', 'like', "%{$searchQuery}%")
                            ->get()
                            ->mapWithKeys(function ($ram) {
                                return [$ram->id => "{$ram->capacity} - {$ram->speed} MHz"];
                            });
                    })
                    ->createOptionForm([
                        TextInput::make('capacity')
                            ->required()
                            ->placeholder('8 GB'),
                        TextInput::make('speed')
                            ->required()
                            ->placeholder('DDR5-5000'),
                    ]),
                Select::make('os_id')
                    ->label('Operating System')
                    ->options(OperatingSystem::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('type')->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $operatingSystem = OperatingSystem::create($data);
                        return $operatingSystem->id;
                    }),
                Select::make('vpn_id')
                    ->label('VPN')
                    ->relationship('vpn', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('pass')->required(),
                    ]),

                // Disks Repeater
                Forms\Components\Repeater::make('disks')
                    ->label('Disks')
                    ->schema([
                        TextInput::make('disk_name')
                            ->label('Disk Name')
                            ->required()
                            ->placeholder('Disk 1'),
                        TextInput::make('capacity')
                            ->label('Capacity')
                            ->required()
                            ->placeholder('256 Gb'),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'HDD' => 'HDD',
                                'SSD' => 'SSD',
                                'NVMe SSD' => 'NVMe SSD',
                                'SATA SSD' => 'SATA SSD',
                            ])
                            ->required(),
                        TextInput::make('speed')
                            ->label('Speed')
                            ->nullable()
                            ->placeholder('5400 Rpm or 1050Mb/s'),
                    ])
                    ->collapsible()
                    ->addActionLabel('Add Disk')
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->getStateUsing(fn($record) => $record->branch->name ?? 'N/A')
                    ->sortable()
                    ->searchable()
                    ->hidden(),
                Tables\Columns\TextColumn::make('branch.code')
                    ->label('Branch')
                    ->getStateUsing(fn($record) => $record->branch->code ?? 'N/A')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pc_code')
                    ->label('PC Code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('imei')
                    ->label('Serial')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->label('PC Name'),
                Tables\Columns\TextColumn::make('computerModel.brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('computerModel.brand_id')->label('Model')
                    ->getStateUsing(fn($record) => $record->computerModel->name ?? 'N/A')->sortable(),
                Tables\Columns\TextColumn::make('cpu_details')
                    ->label('CPU Details')
                    ->getStateUsing(function ($record) {
                        if ($record->cpu) {
                            return $record->cpu->company . ' ' . $record->cpu->name . ' ' . $record->cpu->core . ' ' . $record->cpu->speed . ' ' . 'Gen ' . $record->cpu->gen;
                        }
                        return 'N/A';
                    }),
                Tables\Columns\TextColumn::make('ram.capacity')->label('RAM')
                    ->getStateUsing(fn($record) => $record->ram->capacity ?? 'N/A')->sortable(),
                Tables\Columns\TextColumn::make('disks')
                    ->label('Disks')
                    ->getStateUsing(function ($record) {
                        $state = $record->disks;
                        if (is_array($state) && !empty($state)) {
                            // Map each disk's information into the desired format
                            $formattedDisks = array_map(function ($disk) {
                                $diskName = $disk['disk_name'] ?? 'Unnamed Disk';
                                $capacity = $disk['capacity'] ?? 'Unknown Capacity';
                                $type = $disk['type'] ?? 'Unknown Type';
                                $interface = $disk['speed'] ?? 'Unknown Interface';

                                // Combine into the desired format
                                return "{$diskName} : {$capacity} {$type} {$interface}";
                            }, $state);

                            // Join each formatted disk's information with a new line
                            return implode('<br />', $formattedDisks);
                        }
                        return 'No Disks';
                    })
                    ->html(), // This enables HTML rendering for the column
                Tables\Columns\TextColumn::make('os.name')->label('OS')
                    ->getStateUsing(fn($record) => $record->os->name ?? 'N/A')->sortable(),
                Tables\Columns\TextColumn::make('vpn.name')
                    ->label('VPN Name')->searchable(),
                Tables\Columns\TextColumn::make('vpn.pass')
                    ->label('VPN Code')
                    ->visible(fn() => Filament::auth()->user()->hasRole('super_admin')),
                BadgeColumn::make('warranty_status')
                    ->label('Warranty Status')
                    ->getStateUsing(function ($record) {
                        // Calculate expiry date by adding warranty months to purchase_date
                        $expiryDate = Carbon::parse($record->purchase_date)->addMonths($record->warranty);
                        $currentDate = Carbon::now();
                        // Calculate the difference in days (as integer)
                        $daysDifference = $currentDate->diffInDays($expiryDate, false);
                        // Return the number of days remaining or expired (without decimals)
                        if ($daysDifference >= 0) {
                            return intval($daysDifference) . ' days Bal';
                        } else {
                            return intval(abs($daysDifference)) . ' days Exp';
                        }
                    })
                    ->colors([
                        'success' => fn($state) => str_contains($state, 'Bal'), // Green if warranty is valid
                        'danger' => fn($state) => str_contains($state, 'Exp'), // Red if warranty has expired
                    ]),
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
