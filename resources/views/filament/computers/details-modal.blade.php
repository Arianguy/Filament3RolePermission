<!-- resources/views/filament/computers/details-modal.blade.php -->

<div class="modal-content-custom bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <!-- Header Section -->
    <div class="mb-4">
        <h2 class="text-lg font-bold text-blue-600 dark:text-blue-400 mb-1">Computer Details: {{ $record->name }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-300">Detailed information about the selected computer.</p>
    </div>

    <!-- Basic Information Section -->
    <div class="mb-4">
        <h3 class="section-title">Basic Information</h3>
        <div class="flex flex-wrap -mx-1">
            <!-- Branch -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Branch:</strong>
                    <p>{{ $record->branch->name ?? 'N/A' }}</p>
                </div>
            </div>
            <!-- PC Code -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>PC Code:</strong>
                    <p>{{ $record->pc_code }}</p>
                </div>
            </div>
            <!-- Computer Name -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Computer Name:</strong>
                    <p>{{ $record->name }}</p>
                </div>
            </div>
            <!-- IMEI -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>IMEI / Serial No:</strong>
                    <p>{{ $record->imei }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Details and Warranty Section -->
    <div class="mb-4">
        <h3 class="section-title">Purchase Details and Warranty</h3>
        <div class="flex flex-wrap -mx-1">
            <!-- Cost -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Cost (local Currency):</strong>
                    <p>{{ number_format($record->cost, 2) }}</p>
                </div>
            </div>
            <!-- Purchase Date -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Purchase Date:</strong>
                    <p>{{ $record->purchase_date->format('Y-m-d') }}</p>
                </div>
            </div>
            <!-- Warranty -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Warranty (Months):</strong>
                    <p>{{ $record->warranty }} months</p>
                </div>
            </div>
            <!-- BYOD -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>BYOD:</strong>
                    <p>{{ $record->byod ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Specifications Section -->
    <div class="mb-4">
        <h3 class="section-title">Technical Specifications</h3>
        <div class="flex flex-wrap -mx-1">
            <!-- Category -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Category:</strong>
                    <p>{{ $record->category->name ?? 'N/A' }}</p>
                </div>
            </div>
            <!-- Model -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>Model:</strong>
                    <p>{{ optional($record->computerModel->brand)->name }} {{ $record->computerModel->name ?? 'N/A' }}</p>
                </div>
            </div>
            <!-- CPU -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>CPU:</strong>
                    <p>{{ $record->cpu->name ?? 'N/A' }}</p>
                </div>
            </div>
            <!-- RAM -->
            <div class="w-1/2 p-1">
                <div class="card dark-mode">
                    <strong>RAM:</strong>
                    <p>{{ $record->ram->capacity ?? 'N/A' }} - {{ $record->ram->speed ?? 'N/A' }} MHz</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Software Installations Section -->
    <div class="mb-4">
        <h3 class="section-title">Software Installations</h3>
        <div class="flex flex-wrap -mx-1">
            @foreach ($record->installations as $installation)
                <div class="w-1/2 p-1">
                    <div class="card dark-mode">
                        <strong>Software:</strong>
                        <p>{{ optional($installation->license->software)->name ?? 'N/A' }}</p>
                        <strong>License Key:</strong>
                        <p>{{ $installation->key ?? 'N/A' }}</p>
                        <strong>User ID:</strong>
                        <p>{{ $installation->userid ?? 'N/A' }}</p>
                        <strong>Password:</strong>
                        <p>{{ $installation->password ?? 'N/A' }}</p>
                        <strong>Assigned Date:</strong>
                        <p>{{ optional($installation->assigned_at)->format('Y-m-d') ?? 'N/A' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
