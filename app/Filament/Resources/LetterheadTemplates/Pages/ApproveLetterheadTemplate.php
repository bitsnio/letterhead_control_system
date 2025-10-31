<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class ApproveLetterheadTemplate extends ViewRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected string $view = 'filament.resources.print-template-resource.pages.approve-print-template';

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Template Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Template Name')
                            // Use the string literal 'lg' or 'xl' instead of the deprecated enum
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('category')
                            ->badge()
                            ->label('Category'),

                        TextEntry::make('createdBy.name')
                            ->label('Created By'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            // Use a consistent type hint for clarity
                            ->visible(fn(Model $record): bool => !empty($record->description)),
                    ])->columns(2),

                Section::make('Template Preview')
                    ->schema([
                        TextEntry::make('content')
                            ->label('')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Variables')
                    ->schema([
                        TextEntry::make('variables')
                            ->label('Detected Variables')
                            ->badge()
                            ->separator(',')
                            // Update the callback signature to use $state and optional $record
                            ->formatStateUsing(fn(string $state): string => '$' . $state . '$')
                            ->color('info')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Model $record): bool => !empty($record->variables)),

                Section::make('Current Approval Status')
                    ->schema([
                        // Use RepeatableEntry for displaying nested data
                        Infolists\Components\RepeatableEntry::make('approvals')
                            ->label('')
                            ->schema([
                                TextEntry::make('level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('approver.name')
                                    ->label('Approver'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    }),
                                TextEntry::make('comments')
                                    ->label('Comments')
                                    ->default('No comments'),
                                TextEntry::make('actioned_at')
                                    ->label('Actioned At')
                                    ->dateTime()
                                    ->default('Pending'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }


    protected function getHeaderActions(): array
    {
        $canApprove = $this->record->approval_status === 'pending' &&
            $this->record->canBeApprovedBy(auth()->user());

        return [
            Actions\Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Template')
                ->modalDescription('Are you sure you want to approve this template?')
                ->form([
                    \Filament\Forms\Components\Textarea::make('comments')
                        ->label('Comments (Optional)')
                        ->rows(3)
                        ->placeholder('Add any comments about this approval...'),
                ])
                ->action(function (array $data) {
                    $this->record->approve(auth()->user(), $data['comments'] ?? null);

                    Notification::make()
                        ->success()
                        ->title('Template Approved')
                        ->body('The template has been approved successfully.')
                        ->send();

                    return redirect()->route('filament.admin.pages.pending-approvals');
                })
                ->visible($canApprove),

            Actions\Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Template')
                ->modalDescription('Please provide a reason for rejecting this template.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3)
                        ->placeholder('Explain why this template is being rejected...'),
                ])
                ->action(function (array $data) {
                    $this->record->reject(auth()->user(), $data['reason']);

                    Notification::make()
                        ->warning()
                        ->title('Template Rejected')
                        ->body('The template has been rejected.')
                        ->send();

                    return redirect()->route('filament.admin.pages.pending-approvals');
                })
                ->visible($canApprove),

            Actions\Action::make('back')
                ->label('Back to Pending Approvals')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.pages.pending-approvals')),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if (!$record) {
            return false;
        }

        // Only allow access if user can approve this template
        return $record->approval_status === 'pending' &&
            $record->canBeApprovedBy(auth()->user());
    }
}
