<?php

namespace App\Filament\Resources\ComputerLicenseUsageResource\Pages;

use App\Filament\Resources\ComputerLicenseUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComputerLicenseUsage extends EditRecord
{
    protected static string $resource = ComputerLicenseUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
