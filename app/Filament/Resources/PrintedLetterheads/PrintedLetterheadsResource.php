<?php

namespace App\Filament\Resources\PrintedLetterheads;

use App\Filament\Resources\PrintedLetterheads\Pages;
use App\Models\SerialUsage;
use App\Models\LetterheadTemplate;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use Filament\Actions;

class PrintedLetterheadsResource extends Resource
{
    protected static ?string $model = SerialUsage::class;

    protected static BackedEnum|null|string $navigationIcon = 'heroicon-o-document-text';
    // protected static ?string $navigationGroup = 'Print Management';
    protected static ?string $navigationLabel = 'Printed Letterheads';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.printed-letterhead-preview';


    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Serial Information')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->disabled(fn($record) => !$record?->canEditSerial())
                            ->helperText(fn($record) => $record ? "Valid range: {$record->printJob->start_serial} - {$record->printJob->end_serial}" : '')
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $record = $get('record');
                                        if ($record && $record->printJob) {
                                            $startSerial = $record->printJob->start_serial;
                                            $endSerial = $record->printJob->end_serial;

                                            // Check if within job's serial range
                                            if ($value < $startSerial || $value > $endSerial) {
                                                $fail("Serial number must be between {$startSerial} and {$endSerial} for this print job.");
                                            }

                                            // Check if serial already exists in the same batch (excluding current record)
                                            $existing = SerialUsage::where('serial_number', $value)
                                                ->where('letterhead_inventory_id', $record->letterhead_inventory_id)
                                                ->where('id', '!=', $record->id)
                                                ->exists();

                                            if ($existing) {
                                                $fail("Serial number {$value} is already used in this letterhead batch.");
                                            }
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\Select::make('print_job_id')
                            ->label('Print Job')
                            ->relationship('printJob', 'id')
                            ->getOptionLabelFromRecordUsing(fn($record) => "Job #{$record->id} ({$record->start_serial}-{$record->end_serial})")
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('letterhead_inventory_id')
                            ->label('Letterhead Batch')
                            ->relationship('letterhead', 'batch_name')
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Forms\Components\FileUpload::make('scanned_copy')
                            ->label('Scanned Copy')
                            ->directory('serial-scans')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->previewable(true)
                            ->helperText('Upload scanned copy (PDF, JPG, PNG, max 5MB)'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add any notes about this serial...'),

                        Forms\Components\DateTimePicker::make('used_at')
                            ->label('Used Date')
                            ->default(now())
                            ->required()
                            ->disabled(fn($record) => !$record?->canEditSerial()),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial No.')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => number_format($state)),

                Tables\Columns\TextColumn::make('printJob.id')
                    ->label('Print Job')
                    ->formatStateUsing(fn($state) => "Job #{$state}")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('letterhead.batch_name')
                    ->label('Batch Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('printJob.templates_count')
                    ->label('Templates')
                    ->formatStateUsing(fn($state, $record) => count($record->printJob->templates ?? [])),

                Tables\Columns\TextColumn::make('used_at')
                    ->label('Used Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('scanned_copy')
                    ->label('Scanned')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('printJob.status')
                    ->label('Job Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('print_job_id')
                    ->label('Print Job')
                    ->relationship('printJob', 'id')
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => "Job #{$record->id} ({$record->start_serial}-{$record->end_serial})"),

                Tables\Filters\SelectFilter::make('letterhead_inventory_id')
                    ->label('Letterhead Batch')
                    ->relationship('letterhead', 'batch_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('has_scanned_copy')
                    ->label('Has Scanned Copy')
                    ->query(fn(Builder $query) => $query->whereNotNull('scanned_copy')),

                Tables\Filters\Filter::make('missing_scanned_copy')
                    ->label('Missing Scanned Copy')
                    ->query(fn(Builder $query) => $query->whereNull('scanned_copy')),

                Tables\Filters\Filter::make('serial_range')
                    ->schema([
                        Forms\Components\TextInput::make('start_serial')
                            ->label('Start Serial')
                            ->numeric(),
                        Forms\Components\TextInput::make('end_serial')
                            ->label('End Serial')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_serial'],
                                fn(Builder $query, $start): Builder => $query->where('serial_number', '>=', $start),
                            )
                            ->when(
                                $data['end_serial'],
                                fn(Builder $query, $end): Builder => $query->where('serial_number', '<=', $end),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('preview_template')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('Template Preview')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        View::make('filament.pages.template-preview')
                            ->viewData([
                                'serialUsage' => fn($record) => $record,
                            ]),
                    ]),

                Actions\EditAction::make()->visible(fn ($record) => $record->canEditSerial()),
                Actions\DeleteAction::make()->visible(fn ($record) => $record->canEditSerial()),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),

                    Actions\BulkAction::make('upload_scans')
                        ->label('Upload Scans')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('primary')
                        ->schema([
                            Forms\Components\FileUpload::make('scanned_copies')
                                ->label('Scanned Copies')
                                ->multiple()
                                ->directory('serial-scans')
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                                ->maxSize(5120)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            // This would need custom implementation for bulk upload
                        }),
                ]),
            ])
            ->defaultSort('used_at', 'desc')
            ->emptyStateHeading('No serial usage records found')
            ->emptyStateDescription('Serial usage records will appear here after print jobs are completed.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrintedLetterheads::route('/'),
            'create' => Pages\CreatePrintedLetterheads::route('/create'),
            'edit' => Pages\EditPrintedLetterheads::route('/{record}/edit'),
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['printJob', 'letterhead'])
            ->orderBy('print_job_id', 'desc')
            ->orderBy('serial_number', 'asc');
    }
}
