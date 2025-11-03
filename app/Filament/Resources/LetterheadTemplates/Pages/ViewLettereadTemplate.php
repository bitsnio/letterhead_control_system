<?php

namespace App\Filament\Resources\LetterheadTemplates\Pages;

use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Infolists;
use Filament\Schemas\Schema;
use DateTime;


class ViewLetterheadTemplate extends ViewRecord
{
    protected static string $resource = LetterheadTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(
                    fn() =>
                    $this->record->approval_status === 'pending' ||
                        $this->record->approval_status === 'rejected'
                ),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Template Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Template Name'),
                        Infolists\Components\TextEntry::make('category')
                            ->badge()
                            ->label('Category'),
                        Infolists\Components\TextEntry::make('approval_status')
                            ->badge()
                            ->label('Status')
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                            }),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active'),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Template Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('content')
                            ->label('Content')
                            ->html()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('variables')
                            ->label('Variables')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->formatStateUsing(fn($state) => '$' . $state . '$')
                            ->columnSpanFull(),
                    ]),

                Section::make('Approval Tracking')
                    ->schema([
                        Infolists\Components\TextEntry::make('createdBy.name')
                            ->label('Created By'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('approvedBy.name')
                            ->label('Approved By')
                            ->default('Not approved yet'),
                        Infolists\Components\TextEntry::make('approved_at')
                            ->label('Approved At')
                            // $state will be a DateTime object or null
                            ->formatStateUsing(function ($state) {
                                if ($state instanceof DateTime) {
                                    // Use the built-in format method of the DateTime object
                                    return $state->format('M d, Y H:i');
                                }

                                return 'Not approved yet';
                            }),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn($record) => $record->approval_status === 'rejected')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Approval History')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('approvals')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('approver.name')
                                    ->label('Approver'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    }),
                                Infolists\Components\TextEntry::make('comments')
                                    ->label('Comments')
                                    ->default('No comments'),
                                Infolists\Components\TextEntry::make('actioned_at')
                                    ->label('Actioned At')
                                    ->formatStateUsing(fn($state): string => $state ? $state->toFormattedDateString() : 'Pending'),
                            ])
                            ->columns(5),
                    ]),
            ]);
    }
}
