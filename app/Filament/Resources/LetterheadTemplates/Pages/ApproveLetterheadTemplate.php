<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ApproveLetterheadTemplate extends ViewRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected string $view = 'filament.resources.letterhead-template-resource.pages.approve-letterhead-template';

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                // Basic Template Information - Full Width with Grid Layout
                Section::make('Template Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Template Name')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-document-text')
                            ->iconColor('primary')
                            ->columnSpan(2),

                        TextEntry::make('category')
                            ->badge()
                            ->label('Category')
                            ->color(fn (string $state): string => match ($state) {
                                'certificate' => 'success',
                                'letter' => 'info',
                                'invoice' => 'warning',
                                'report' => 'primary',
                                default => 'gray',
                            }),

                        TextEntry::make('approval_status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->icon(fn (string $state): string => match ($state) {
                                'pending' => 'heroicon-o-clock',
                                'approved' => 'heroicon-o-check-circle',
                                'rejected' => 'heroicon-o-x-circle',
                                default => 'heroicon-o-question-mark-circle',
                            }),

                        TextEntry::make('createdBy.name')
                            ->label('Created By')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('created_at')
                            ->label('Submitted On')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('print_settings')
                            ->label('Print Settings')
                            ->state(function (Model $record) {
                                $margins = $record->print_margins ?? [];
                                $orientation = $margins['orientation'] ?? 'portrait';
                                $fontSize = $margins['font_size'] ?? 100;
                                return ucfirst($orientation) . ' • ' . $fontSize . '% Font';
                            })
                            ->icon('heroicon-o-cog-6-tooth')
                            ->color('gray'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->visible(fn(Model $record): bool => !empty($record->description)),
                    ])
                    ->columns(4)
                    ->columnSpan('full')
                    ->collapsible(),

                // Template Content Preview - Full Width
                Section::make('Template Content Preview')
                    ->description('Review the template content below. This is how it will appear when printed.')
                    ->schema([
                        TextEntry::make('content_preview')
                            ->label('')
                            ->state(fn (Model $record) => view('filament.components.approval-content-preview', [
                                'content' => $record->content,
                                'margins' => $record->print_margins ?? [
                                    'top' => '15',
                                    'right' => '15',
                                    'bottom' => '15',
                                    'left' => '15',
                                    'orientation' => 'portrait',
                                    'font_size' => 100
                                ],
                            ])->render())
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),

                // Variables Section - Full Width
                Section::make('Template Variables')
                    ->description('These variables will be replaced with actual values during printing.')
                    ->schema([
                        TextEntry::make('variables_display')
                            ->label('')
                            ->state(fn (Model $record) => view('filament.components.variables-list', [
                                'variables' => $record->variables ?? []
                            ])->render())
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Model $record): bool => !empty($record->variables))
                    ->columnSpan('full')
                    ->collapsible(),

                // Approval Workflow Status - Full Width
                Section::make('Approval Workflow')
                    ->description('Track the approval status at each level.')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('approvals')
                            ->label('')
                            ->schema([
                                TextEntry::make('level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(fn ($state) => "Level {$state}"),
                                
                                TextEntry::make('approver.name')
                                    ->label('Approver')
                                    ->icon('heroicon-o-user-circle'),
                                
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    })
                                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                    ->icon(fn(string $state): string => match ($state) {
                                        'pending' => 'heroicon-o-clock',
                                        'approved' => 'heroicon-o-check-circle',
                                        'rejected' => 'heroicon-o-x-circle',
                                    }),
                                
                                TextEntry::make('comments')
                                    ->label('Comments')
                                    ->default('No comments provided')
                                    ->color('gray')
                                    ->icon('heroicon-o-chat-bubble-left-right'),
                                
                                TextEntry::make('actioned_at')
                                    ->label('Action Date')
                                    ->dateTime('M d, Y H:i')
                                    ->formatStateUsing(fn($state) => $state ? $state->format('M d, Y H:i') : '—')
                                    ->icon('heroicon-o-calendar')
                                    ->placeholder('Pending'),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full')
                    ->collapsible(),
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
        if (!isset($parameters['record'])) {
            return true;
        }

        $record = $parameters['record'] ?? null;

        return $record->approval_status === 'pending' &&
            $record->canBeApprovedBy(auth()->user());
    }
}