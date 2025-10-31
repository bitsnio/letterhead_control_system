<?php


namespace App\Filament\Pages;

use App\Models\PrintTemplate;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use App\Models\LetterheadTemplate;
use Filament\Facades\Filament;
use Filament\Actions\Action;

class PendingApprovals extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected string $view = 'filament.pages.pending-approvals';

    protected static ?string $navigationLabel = 'Pending Approvals';

    protected static ?string $title = 'Pending Approvals';

    protected static string|UnitEnum|null $navigationGroup = 'Approvals';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = LetterheadTemplate::whereHas('approvals', function ($query) {
            $query->where('approver_id', auth()->id())
                  ->where('status', 'pending');
        })->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LetterheadTemplate::query()
                    ->whereHas('approvals', function ($query) {
                        $query->where('approver_id', auth()->id())
                              ->where('status', 'pending');
                    })
                    ->with(['createdBy', 'approvals'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Template Name')
                    ->weight('bold')
                    ->description(fn (LetterheadTemplate $record): string => $record->description ?? ''),
                
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->label('Category')
                    ->color(fn (string $state): string => match ($state) {
                        'certificate' => 'success',
                        'letter' => 'info',
                        'invoice' => 'warning',
                        'report' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('current_level')
                    ->label('Approval Level')
                    ->state(function (LetterheadTemplate $record) {
                        $currentApproval = $record->getCurrentPendingApproval();
                        return 'Level ' . ($currentApproval?->level ?? 'N/A');
                    })
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Submitted On')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'certificate' => 'Certificate',
                        'letter' => 'Letter',
                        'invoice' => 'Invoice',
                        'report' => 'Report',
                        'other' => 'Other',
                    ])
                    ->label('Category'),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (LetterheadTemplate $record): string => 
                        route('filament.admin.resources.print-templates.approve', ['record' => $record])
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No Pending Approvals')
            ->emptyStateDescription('You have no templates waiting for your approval.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}