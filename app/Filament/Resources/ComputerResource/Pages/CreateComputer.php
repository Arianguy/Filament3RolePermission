<?php

namespace App\Filament\Resources\ComputerResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ComputerResource;

class CreateComputer extends CreateRecord
{
    protected static string $resource = ComputerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check if disks are present
        if (!empty($data['disks'])) {
            foreach ($data['disks'] as $index => &$disk) {
                if (empty($disk['disk_name'])) {
                    $disk['disk_name'] = 'Disk:' . ($index + 1);
                }
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Computer created successfully')
            ->success()
            ->send();
    }
}
