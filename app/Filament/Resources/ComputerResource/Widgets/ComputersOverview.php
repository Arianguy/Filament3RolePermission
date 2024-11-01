<?php

namespace App\Filament\Resources\ComputerResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Computer;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ComputersOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static string $permissionName = 'widget_ComputersOverview';

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $query = $this->getQueryForUserScope($user);

        return [
            $this->getTotalComputersStat($query, $user),
            $this->getByodComputersStat($query),
            $this->getCostStat($query),
        ];
    }

    private function getQueryForUserScope($user)
    {
        $query = Computer::query();

        // Super admin sees everything
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Get all assignments
        $assignedCountryIds = $user->countries->pluck('id')->toArray();
        $assignedRegionIds = $user->regions->pluck('id')->toArray();
        $assignedBranchIds = $user->branches->pluck('id')->toArray();

        // Start with an empty array for branch IDs
        $relevantBranchIds = [];

        // If user has country assignments, get all branches in those countries
        if (!empty($assignedCountryIds)) {
            $countryBranchIds = Branch::whereIn('country_id', $assignedCountryIds)
                ->pluck('id')
                ->toArray();
            $relevantBranchIds = array_merge($relevantBranchIds, $countryBranchIds);
        }

        // If user has region assignments, get all branches in those regions
        if (!empty($assignedRegionIds)) {
            $regionBranchIds = Branch::whereIn('region_id', $assignedRegionIds)
                ->pluck('id')
                ->toArray();
            $relevantBranchIds = array_merge($relevantBranchIds, $regionBranchIds);
        }

        // Add directly assigned branches
        if (!empty($assignedBranchIds)) {
            $relevantBranchIds = array_merge($relevantBranchIds, $assignedBranchIds);
        }

        // Remove duplicates
        $relevantBranchIds = array_unique($relevantBranchIds);

        // If we have any branch IDs, filter computers by these branches
        if (!empty($relevantBranchIds)) {
            $query->whereIn('branch_id', $relevantBranchIds);
        } else {
            // If no branches found in any scope, return empty result
            $query->where('id', 0);
        }

        return $query;
    }

    private function getTotalComputersStat($query, $user): Stat
    {
        $description = $this->getScopeDescription($user);

        return Stat::make('Total Computers', $query->count())
            ->description($description)
            ->descriptionIcon('heroicon-m-computer-desktop')
            ->color('primary');
    }

    private function getByodComputersStat($query): Stat
    {
        $byodCount = $query->clone()->where('byod', true)->count();
        $companyCount = $query->clone()->where('byod', false)->count();

        return Stat::make('BYOD vs Company', "$byodCount / $companyCount")
            ->description('BYOD / Company Owned')
            ->descriptionIcon('heroicon-m-device-phone-mobile')
            ->color('success');
    }

    private function getCostStat($query): Stat
    {
        $totalCost = $query->clone()->sum('cost');
        $formattedCost = number_format($totalCost, 2);

        return Stat::make('Total Investment', "$ {$formattedCost}")
            ->description('Total computer costs')
            ->descriptionIcon('heroicon-m-currency-dollar')
            ->color('warning');
    }

    private function getScopeDescription($user): string
    {
        if ($user->hasRole('super_admin')) {
            return 'All Computers';
        }

        $descriptions = [];

        // Add country descriptions
        if ($user->countries->isNotEmpty()) {
            $countryNames = $user->countries->pluck('name')->join(', ');
            $descriptions[] = "Countries: {$countryNames}";
        }

        // Add region descriptions
        if ($user->regions->isNotEmpty()) {
            $regionNames = $user->regions->pluck('name')->join(', ');
            $descriptions[] = "Regions: {$regionNames}";
        }

        // Add branch descriptions
        if ($user->branches->isNotEmpty()) {
            $branchNames = $user->branches->pluck('name')->join(', ');
            $descriptions[] = "Branches: {$branchNames}";
        }

        if (empty($descriptions)) {
            return 'No Assignments';
        }

        return 'Computers in ' . implode(' | ', $descriptions);
    }
}
