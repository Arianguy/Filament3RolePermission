<!-- resources/views/filament/computers/details-modal.blade.php -->
<div class="bg-gray-50 dark:bg-gray-900 rounded-xl shadow-xl p-8">
    <!-- Header Section (unchanged) -->
    <header class="mb-8 border-b border-gray-200 dark:border-gray-700 pb-4">
        <h2 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">
            Computer Details: {{ $record->name }}
        </h2>
        <p class="text-gray-600 dark:text-gray-300">
            Comprehensive information about the selected computer
        </p>
    </header>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Basic Information (unchanged) -->
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4 text-indigo-500 dark:text-indigo-300">
                Basic Information
            </h3>
            <div class="space-y-4">
                @php
                    $basicInfo = [
                        'Branch' => $record->branch->name ?? 'N/A',
                        'PC Code' => $record->pc_code,
                        'Computer Name' => $record->name,
                        'IMEI / Serial No' => $record->imei,
                    ];
                @endphp
                @foreach ($basicInfo as $label => $value)
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                        <strong class="text-gray-600 dark:text-gray-400">{{ $label }}</strong>
                        <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Middle Column: Technical Specifications, Disks & Purchase Details -->
        <section class="space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 text-indigo-500 dark:text-indigo-300">
                    Technical Specifications
                </h3>
                <div class="space-y-4">
                    @php
                        $techSpecs = [
                            'Category' => $record->category->name ?? 'N/A',
                            'Model' => (optional($record->computerModel->brand)->name . ' ' . ($record->computerModel->name ?? 'N/A')),
                            'CPU' => $record->cpu->name ?? 'N/A',
                            'RAM' => ($record->ram->capacity ?? 'N/A') . ' - ' . ($record->ram->speed ?? 'N/A') . ' MHz',
                        ];
                    @endphp
                    @foreach ($techSpecs as $label => $value)
                        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <strong class="text-gray-600 dark:text-gray-400">{{ $label }}</strong>
                            <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Disks Information -->
                <h4 class="text-lg font-semibold mt-6 mb-3 text-indigo-500 dark:text-indigo-300">
                    Disks
                </h4>
                <div class="space-y-2">
                    @foreach ($record->disks ?? [] as $disk)
                        <div class="flex items-center space-x-2 text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $disk['disk_name'] }}:</span>
                            <span class="text-gray-800 dark:text-gray-200">{{ $disk['capacity'] }} {{ $disk['type'] }}</span>
                            @if($disk['speed'])
                                <span class="text-gray-600 dark:text-gray-400">({{ $disk['speed'] }})</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-4 text-indigo-500 dark:text-indigo-300">
                    Purchase Details and Warranty
                </h3>
                <div class="space-y-4">
                    @php
                        $purchaseInfo = [
                            'Cost' => number_format($record->cost, 2) . ' (local currency)',
                            'Purchase Date' => $record->purchase_date->format('Y-m-d'),
                            'Warranty' => $record->warranty . ' months',
                            'BYOD' => $record->byod ? 'Yes' : 'No',
                        ];
                    @endphp
                    @foreach ($purchaseInfo as $label => $value)
                        <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <strong class="text-gray-600 dark:text-gray-400">{{ $label }}</strong>
                            <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Right Column: Software Installations (unchanged) -->
        <section class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold mb-4 text-indigo-500 dark:text-indigo-300">
                Software Installations
            </h3>
            <div class="space-y-6">
                @foreach ($record->installations as $installation)
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-b-0 last:pb-0">
                        <h4 class="font-semibold text-lg mb-2 text-indigo-600 dark:text-indigo-400">
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
                            <div class="flex justify-between items-center mb-2">
                                <strong class="text-gray-600 dark:text-gray-400">{{ $label }}</strong>
                                <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
