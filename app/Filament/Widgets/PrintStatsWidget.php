<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PrintStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRequests = \App\Models\PrintRequest::count();
        $pendingRequests = \App\Models\PrintRequest::pending()->count();
        $printingRequests = \App\Models\PrintRequest::printing()->count();
        $completedRequests = \App\Models\PrintRequest::completed()->count();
        $totalPrints = \App\Models\PrintResult::sum('successful_prints');
        $totalWasted = \App\Models\PrintResult::sum('wasted_prints');

        return [
            Stat::make('Total Print Requests', $totalRequests)
                ->description('All print requests')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            
            Stat::make('Pending Requests', $pendingRequests)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Currently Printing', $printingRequests)
                ->description('In progress')
                ->descriptionIcon('heroicon-m-printer')
                ->color('info'),
            
            Stat::make('Completed Requests', $completedRequests)
                ->description('Successfully completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Total Successful Prints', number_format($totalPrints))
                ->description('All successful prints')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            
            Stat::make('Total Wasted Prints', number_format($totalWasted))
                ->description('Wasted prints')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
