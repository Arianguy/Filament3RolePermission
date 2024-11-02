<?php

namespace App\Filament\Resources\ComputerResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Computer;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;

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
        $user = Auth::user();
        $totalUsdCost = 0;

        // Clone the query and eager load relationships
        $computersQuery = $query->clone()->with(['branch.country']);
        $computers = $computersQuery->get();

        foreach ($computers as $computer) {
            if ($computer->branch && $computer->branch->country) {
                $country = $computer->branch->country;
                $totalUsdCost += $computer->cost / $country->exc_rate;
            } else {
                $totalUsdCost += $computer->cost;
            }
        }

        $formattedCost = number_format($totalUsdCost, 2);
        $description = $this->getCostDescription($user);

        return Stat::make('Total Investment (USD)', "$ {$formattedCost}")
            ->description($description)
            ->descriptionIcon('heroicon-m-currency-dollar')
            ->color('warning')
            ->chart($this->getInvestmentTrend($query));
    }

    private function getCostDescription($user): string
    {
        if ($user->hasRole('super_admin')) {
            return 'Total investment across all countries';
        }

        $descriptions = [];

        // Check country assignments
        if ($user->countries->isNotEmpty()) {
            $countryInfo = $user->countries->map(function ($country) {
                $total = Computer::whereHas('branch', function ($query) use ($country) {
                    $query->where('country_id', $country->id);
                })->sum('cost');
                $usdTotal = $total / $country->exc_rate;
                return "{$country->name}: $" . number_format($usdTotal, 2);
            })->join(', ');
            $descriptions[] = "Countries: {$countryInfo}";
        }

        // Check region assignments
        if ($user->regions->isNotEmpty()) {
            $regionInfo = $user->regions->map(function ($region) {
                $total = Computer::whereHas('branch', function ($query) use ($region) {
                    $query->where('region_id', $region->id);
                })->sum('cost');
                $usdTotal = $total / $region->country->exc_rate;
                return "{$region->name}: $" . number_format($usdTotal, 2);
            })->join(', ');
            $descriptions[] = "Regions: {$regionInfo}";
        }

        // Check branch assignments
        if ($user->branches->isNotEmpty()) {
            $branchInfo = $user->branches->map(function ($branch) {
                $total = Computer::where('branch_id', $branch->id)->sum('cost');
                $usdTotal = $total / $branch->country->exc_rate;
                return "{$branch->name}: $" . number_format($usdTotal, 2);
            })->join(', ');
            $descriptions[] = "Branches: {$branchInfo}";
        }

        if (empty($descriptions)) {
            return 'No assigned scope';
        }

        return implode(' | ', $descriptions);
    }

    private function getInvestmentTrend($query): array
    {
        $trends = [];
        $startDate = now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $monthlyTotal = $query->clone()
                ->whereYear('purchase_date', $date->year)
                ->whereMonth('purchase_date', $date->month)
                ->with(['branch.country'])
                ->get()
                ->sum(function ($computer) {
                    return $computer->cost / ($computer->branch->country->exc_rate ?? 1);
                });

            $trends[] = $monthlyTotal;
        }

        return $trends;
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
