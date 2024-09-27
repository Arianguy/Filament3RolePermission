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
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
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



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Branch and Basic Information Section
                Forms\Components\Section::make('Basic Information')
                    //->description('Provide the primary details of the computer.')
                    ->schema([
                        Forms\Components\Grid::make(2)  // Two-column layout
                            ->schema([
                                Select::make('branch_id')
                                    ->label('Branch')
                                    ->options(function () {
                                        $user = Auth::user();
                                        // Fetch user branches, regions, and countries
                                        $branchIds = $user->branches()->pluck('branches.id')->toArray();
                                        $regionIds = $user->regions()->pluck('regions.id')->toArray();
                                        $countryIds = $user->countries()->pluck('countries.id')->toArray();

                                        // Fetch branches the user has access to
                                        return Branch::query()
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
                                    })
                                    ->searchable()
                                    ->required()
                                    ->default(function () {
                                        $user = Auth::user();
                                        $branchIds = $user->branches()->pluck('branches.id')->toArray();
                                        if (count($branchIds) === 1) {
                                            return $branchIds[0];
                                        }
                                        return null;
                                    })
                                    ->helperText('Branch selection is based on user privileges.'),
                                TextInput::make('pc_code')
                                    ->label('PC Code')
                                    ->required()
                                    ->readOnly()
                                    ->default(fn() => strtoupper(Str::random(5)))
                                    ->helperText('Auto-generated unique code for this computer.'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Computer Name')
                                    ->required()
                                    ->placeholder('Enter the computer name'),
                                TextInput::make('imei')
                                    ->label('IMEI / Serial No')
                                    ->required()
                                    ->placeholder('Enter IMEI or Serial number'),
                            ]),
                    ])
                    ->collapsible(),

                // Purchase and Warranty Section
                Forms\Components\Section::make('Purchase Details and Warranty details')
                    //->description('Enter the purchase and warranty details for the computer.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('cost')
                                    ->label('Cost (local Currency)')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Enter the purchase cost in Local Currency'),
                                DatePicker::make('purchase_date')
                                    ->label('Purchase Date')
                                    ->required(),
                                // ->hint('When was this computer purchased?'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('warranty')
                                    ->label('Warranty (Months)')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Enter warranty duration in months'),
                                Toggle::make('byod')
                                    ->label('BYOD (Bring Your Own Device)')
                                    ->default(false)
                                    ->hint('Check this if the computer is a personal device used for work (BYOD).'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Technical Specifications Section
                Forms\Components\Section::make('Technical Specifications')
                    // ->description('Enter the technical details like CPU, RAM, and category.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')->required()->placeholder('e.g., Laptop'),
                                    ]),
                                Select::make('model_id')
                                    ->label('Model')
                                    ->relationship('computerModel', 'name', function ($query) {
                                        $query->with('brand');
                                    })
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return "{$record->brand->name} {$record->name}";
                                    })
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')->label('Model Name')->required(),
                                        Select::make('brand_id')->label('Brand')->relationship('brand', 'name')->searchable()->required(),
                                    ]),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('cpu_id')
                                    ->label('CPU')
                                    ->relationship('cpu', 'name')
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('core')->required(),
                                        TextInput::make('speed')->required(),
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
                                    }),
                            ]),
                        Forms\Components\Section::make('Disks')
                            ->description('Add the disk details for the computer.')
                            ->schema([
                                Forms\Components\Repeater::make('disks')
                                    ->label('Disks')
                                    ->schema([
                                        //TextInput::make('disk_name')->label('Disk Name')->required()->placeholder('e.g., Disk 1'),
                                        Select::make('type')->label('Type')->options([
                                            'Disk 0' => 'Disk 0',
                                            'Disk 1' => 'Disk 1',
                                            'Disk 2' => 'Disk 2',
                                            'Disk 3' => 'Disk 3',
                                            'Disk 4' => 'Disk 4',
                                        ])->required(),
                                        TextInput::make('capacity')->label('Capacity')->required()->placeholder('e.g., 256 GB')
                                            ->helperText('GB or TB'),
                                        Select::make('type')->label('Type')->options([
                                            'HDD' => 'HDD',
                                            'SSD' => 'SSD',
                                            'NVMe SSD' => 'NVMe SSD',
                                        ])->required(),
                                        TextInput::make('speed')->label('Speed')->nullable()->placeholder('e.g. 1050 MB/s')
                                            ->helperText('GB/s or MB/s'),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Add Disk')
                                    ->collapsible(),
                            ])
                            ->collapsible(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // Software Installations Section
                Forms\Components\Section::make('Software Installations')
                    ->description('Assign software licenses to this computer.')
                    ->schema([
                        Forms\Components\Repeater::make('installations')
                            ->relationship('installations')  // Define the relationship to the Installation model
                            ->schema([
                                Select::make('license_id')
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
                                TextInput::make('key')->label('License Key')->nullable(),
                                TextInput::make('userid')->label('User ID')->nullable(),
                                TextInput::make('password')->label('Password')->nullable(),
                                DatePicker::make('assigned_at')->label('Assigned Date')->nullable(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\MultiSelect::make('emailAccounts')
                    ->relationship('emailAccounts', 'email_address')
                    ->label('Configured Email Accounts'),
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
                SelectFilter::make('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->indicator('Contract '),
            ], layout: FiltersLayout::Modal)

            ->actions([
                ActionGroup::make([
                    // Edit action
                    Tables\Actions\EditAction::make(),
                    // Custom 'Details' action
                    Action::make('details')
                        //->label('Details')
                        ->icon('heroicon-o-eye')
                        // ->modalHeading('Computer Details')
                        ->modalWidth('4xl') // Ensure the modal is wide enough for a two-column layout
                        ->modalContent(function ($record) {
                            return view('filament.computers.details-modal', ['record' => $record]);
                        })
                        ->modalHeading(null) // Remove the modal title
                        ->modalActions([]),
                ]),
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
