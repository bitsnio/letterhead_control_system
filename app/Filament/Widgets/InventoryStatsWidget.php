<?php

namespace App\Filament\Widgets;

use App\Models\LetterheadInventory;
use App\Models\LetterheadTemplate;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Base counts
        $totalInventory = LetterheadInventory::count();
        $totalQuantity = LetterheadInventory::sum('quantity');

        // Template-related counts
        $totalTemplates = LetterheadTemplate::count();
        $approvedTemplates = LetterheadTemplate::where('approval_status', 'approved')->count();
        $pendingTemplates = LetterheadTemplate::where('approval_status', 'pending_approval')->count();
        $rejectedTemplates = LetterheadTemplate::where('approval_status', 'rejected')->count();

        return [
            Stat::make('Total Inventory Batches', $totalInventory)
                ->description('Total received letterhead batches')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Total Letterheads', number_format($totalQuantity))
                ->description('Sum of all serials across batches')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Total Templates', $totalTemplates)
                ->description('All letterhead templates')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('secondary'),

            Stat::make('Approved Templates', $approvedTemplates)
                ->description('Ready for printing')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending Approval', $pendingTemplates)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Rejected Templates', $rejectedTemplates)
                ->description('Templates not approved')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
