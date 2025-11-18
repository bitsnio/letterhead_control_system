<?php

namespace App\Filament\Resources\LetterheadTemplates\Tables;

use App\Filament\Resources\LetterheadInventories\LetterheadInventoryResource;
use App\Filament\Resources\LetterheadTemplates\LetterheadTemplateResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Table;
use App\Models\LetterheadTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;


use Filament\Notifications\Notification;

class LetterheadTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Template Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->label('Category')
                    ->color(fn(string $state): string => match ($state) {
                        'certificate' => 'success',
                        'letter' => 'info',
                        'invoice' => 'warning',
                        'report' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('variables')
                    ->label('Variables')
                    ->badge()
                    ->state(fn($record) => count($record->variables ?? []))
                    ->color('gray')
                    ->formatStateUsing(fn($state) => $state . ' vars'),

                Tables\Columns\TextColumn::make('approval_status')
                    ->badge()
                    ->sortable()
                    ->label('Status')
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At')
                    ->toggleable(isToggledHiddenByDefault: true),
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

                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Approval Status'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->recordActions([
                ViewAction::make(),
                
                EditAction::make()
                    ->visible(fn (LetterheadTemplate $record) => 
                        $record->approval_status === 'pending' || 
                        $record->approval_status === 'rejected'
                    ),
                
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['approval_status', 'approved_by', 'approved_at', 'rejection_reason'])
                    ->beforeReplicaSaved(function (LetterheadTemplate $replica): void {
                        $replica->name = $replica->name . ' (Copy)';
                    })
                    ->visible(fn (LetterheadTemplate $record) => $record->approval_status === 'approved'),
                
                DeleteAction::make()
                    ->visible(fn (LetterheadTemplate $record) => 
                        $record->approval_status !== 'approved'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('selectForPrint')
                        ->label('Select for Print')
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->action(function (Collection $records) {
                            // Store selected template IDs in session
                            session(['selected_templates_for_print' => $records->pluck('id')->toArray()]);
                            
                            return redirect()->route('filament.admin.pages.bulk-print');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // Only show approved and active templates in the list
                return $query->where('approval_status', 'approved')
                            ->where('is_active', true);
            });
    }
}
