<?php

namespace App\Filament\Resources\ComputerLicenseUsageResource\Pages;

use App\Filament\Resources\ComputerLicenseUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComputerLicenseUsages extends ListRecords
{
    protected static string $resource = ComputerLicenseUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
