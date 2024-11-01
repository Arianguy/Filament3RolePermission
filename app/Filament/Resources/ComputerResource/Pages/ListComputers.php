<?php

namespace App\Filament\Resources\ComputerResource\Pages;

use Filament\Actions;
use Widgets\Computer;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ComputerResource;
use App\Filament\Resources\ComputerResource\Widgets\Computers;
use App\Filament\Resources\ComputerResource\Widgets\ComputersOverview;

class ListComputers extends ListRecords
{
    protected static string $resource = ComputerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ComputersOverview::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [
            ComputersOverview::class,
        ];
    }
}
