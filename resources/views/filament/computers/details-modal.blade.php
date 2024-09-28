<!-- resources/views/filament/computers/details-modal.blade.php -->
<div class="bg-gray-100 dark:bg-gray-800 rounded-xl shadow-lg p-6">
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Basic Information -->
        <section class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                Basic Information
            </h3>
            <div class="space-y-3">
                @php
                    $basicInfo = [
                        'Branch' => $record->branch->name ?? 'N/A',
                        'PC Code' => $record->pc_code,
                        'Computer Name' => $record->name,
                        'IMEI / Serial No' => $record->imei,
                    ];
                @endphp
                @foreach ($basicInfo as $label => $value)
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-600 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                        <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Middle Column: Technical Specifications -->
        <section class="space-y-6">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                    Technical Specifications
                </h3>
                <div class="space-y-3">
                    @php
                        $techSpecs = [
                            'Category' => $record->category->name ?? 'N/A',
                            'Model' => (optional($record->computerModel->brand)->name . ' ' . ($record->computerModel->name ?? 'N/A')),
                          'CPU' => $record->cpu ? "{$record->cpu->name} ({$record->cpu->core} cores, {$record->cpu->speed})" : 'N/A',
                            'RAM' => ($record->ram->capacity ?? 'N/A') . ' - ' . ($record->ram->speed ?? 'N/A') . ' MHz',
                        ];
                    @endphp
                    @foreach ($techSpecs as $label => $value)
                        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-600 pb-2">
                            <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                            <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
                <!-- Disks Information -->
                <h4 class="text-md  mt-4 mb-2 text-gray-600 dark:text-gray-400">
                    Disks
                </h4>
                <div class="space-y-2">
                    @foreach ($record->disks ?? [] as $index => $disk)
                        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-600 pb-2">
                            <span class="text-gray-600 dark:text-gray-400 invisible">Placeholder</span>
                            <div class="text-right">
                                <span class="text-gray-600 dark:text-gray-400 mr-2">Disk {{ $index + 1 }}</span>
                                <span class="text-gray-800 dark:text-gray-200">
                                    {{ $disk['capacity'] }} {{ $disk['type'] }}
                                    @if($disk['speed'])
                                        ({{ $disk['speed'] }})
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Purchase Details and Warranty -->
            <section class="space-y-6">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                        Purchase Details and Warranty
                    </h3>
                    <div class="space-y-3">
                        @php
                        $purchaseDate = $record->purchase_date;
                        $warrantyMonths = $record->warranty;
                        $warrantyEndDate = $purchaseDate->copy()->addMonths($warrantyMonths);
                        $today = now();
                        $daysRemaining = $today->diffInDays($warrantyEndDate, false);
                        $daysRounded = (int)$daysRemaining; // Remove decimal
                        $purchaseInfo = [
                            'Cost' => number_format($record->cost, 2) . ' (local currency)',
                            'Purchase Date' => $purchaseDate->format('Y-m-d'),
                            'Warranty' => $warrantyMonths . ' months',
                            'BYOD' => $record->byod ? 'Yes' : 'No',
                        ];
                        @endphp
                        @foreach ($purchaseInfo as $label => $value)
                        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-600 pb-2">
                            <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                            <div class="text-right">
                                @if ($label === 'Warranty')
                                    <span class="text-gray-800 dark:text-gray-200">
                                        {{ $value }}
                                    </span>
                                    <x-filament::badge
                                        :color="$daysRounded > 0 ? 'success' : 'danger'"
                                        class="ml-2"
                                    >
                                        {{ abs($daysRounded) }} days {{ $daysRounded > 0 ? 'remaining' : 'expired' }}
                                    </x-filament::badge>
                                @else
                                    <span class="text-gray-800 dark:text-gray-200">
                                        {{ $value }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>

        <!-- Right Column: Software Installations -->
        <section class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                Software Installations
            </h3>
            <div class="space-y-4">
                @foreach ($record->installations as $installation)
                    <div class="border-b border-gray-200 dark:border-gray-600 pb-3 last:border-b-0 last:pb-0">
                        <h4 class="font-semibold text-md mb-2 text-gray-700 dark:text-gray-300">
                            {{ optional($installation->license->software)->name ?? 'N/A' }}
                        </h4>
                        @php
                            $installationDetails = [
                                'License Key' => $installation->key ?? 'N/A',
                                'User ID' => $installation->userid ?? 'N/A',
                                'Password' => $installation->password ?? 'N/A',
                                'Assigned Date' => optional($installation->assigned_at)->format('Y-m-d') ?? 'N/A',
                            ];
                        @endphp
                        @foreach ($installationDetails as $label => $value)
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
