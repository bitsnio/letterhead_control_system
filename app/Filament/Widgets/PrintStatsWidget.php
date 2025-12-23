<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SerialUsage;

class PrintStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // $totalRequests = \App\Models\PrintRequest::count();
        // $pendingRequests = \App\Models\PrintRequest::pending()->count();
        // $printingRequests = \App\Models\PrintRequest::printing()->count();
        // $completedRequests = \App\Models\PrintRequest::completed()->count();
        // Count total successful prints
        $totalSuccessful = \App\Models\SerialUsage::where('notes', 'successful')->count();

        // Count total wasted prints
        $totalWasted = \App\Models\SerialUsage::where('notes', 'wasted')->count();

        return [
            // Stat::make('Total Print Requests', $totalRequests)
            //     ->description('All print requests')
            //     ->descriptionIcon('heroicon-m-document-text')
            //     ->color('primary'),

            // Stat::make('Pending Requests', $pendingRequests)
            //     ->description('Awaiting approval')
            //     ->descriptionIcon('heroicon-m-clock')
            //     ->color('warning'),

            // Stat::make('Currently Printing', $printingRequests)
            //     ->description('In progress')
            //     ->descriptionIcon('heroicon-m-printer')
            //     ->color('info'),

            // Stat::make('Completed Requests', $completedRequests)
            //     ->description('Successfully completed')
            //     ->descriptionIcon('heroicon-m-check-circle')
            //     ->color('success'),

           Stat::make('Successful Prints', SerialUsage::where('notes', 'successful')->count())
                ->description('Total documents verified')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Wasted Letterheads', SerialUsage::where('notes', 'wasted')->count())
                ->description('Total spoiled/wasted')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
                
            Stat::make('Pending Scans', SerialUsage::whereNull('scanned_copy')->count())
                ->description('Records awaiting update')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
