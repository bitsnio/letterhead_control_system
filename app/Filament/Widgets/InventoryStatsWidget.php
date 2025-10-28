<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalInventory = \App\Models\LetterheadInventory::count();
        $lowStockItems = \App\Models\LetterheadInventory::whereRaw('current_quantity <= minimum_level')->count();
        $outOfStockItems = \App\Models\LetterheadInventory::where('current_quantity', 0)->count();
        $totalTemplates = \App\Models\LetterheadTemplate::count();
        $approvedTemplates = \App\Models\LetterheadTemplate::where('status', 'approved')->count();
        $pendingTemplates = \App\Models\LetterheadTemplate::where('status', 'pending_approval')->count();

        return [
            Stat::make('Total Inventory Items', $totalInventory)
                ->description('All inventory items')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            
            Stat::make('Low Stock Items', $lowStockItems)
                ->description('Items below minimum level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            
            Stat::make('Out of Stock', $outOfStockItems)
                ->description('Items with zero quantity')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            
            Stat::make('Total Templates', $totalTemplates)
                ->description('All letterhead templates')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            
            Stat::make('Approved Templates', $approvedTemplates)
                ->description('Ready for printing')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Pending Approval', $pendingTemplates)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
