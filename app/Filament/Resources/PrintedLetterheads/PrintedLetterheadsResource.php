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
use Illuminate\Support\Facades\Storage;

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
                            ->helperText('Upload scanned copy (PDF, JPG, PNG, max 5MB)')
                            ->disabled(fn($record): bool => !empty($record->notes)),

                        Forms\Components\Select::make('notes') // ← CHANGE THIS from Textarea to Select
                            ->label('Print Status')
                            ->options([
                                'successful' => 'Print Successful',
                                'wasted' => 'Wasted',
                            ])
                            ->nullable()
                            ->placeholder('Select print status...')
                            ->helperText('Once status is set, this record cannot be edited further.')
                            ->disabled(fn($record): bool => !empty($record->notes)),

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
                    ->formatStateUsing(fn($state) => $state),

                Tables\Columns\TextColumn::make('printJob.id')
                    ->label('Print Job')
                    ->formatStateUsing(fn($state) => "Job #{$state}")
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('template_name')
                    ->label('Template')
                    ->state(function ($record): string {
                        static $templateCache = [];

                        $job = $record->printJob;
                        $serial = $record->serial_number;
                        $start = $job->start_serial;

                        // 1. Calculate the Print Sequence (Key in variable_data)
                        // If start is 1000 and current is 1002, this is the 3rd page (Key "3")
                        $pageNumber = ($serial - $start) + 1;
                        $pageKey = (string) $pageNumber;

                        // 2. Access variable_data to see what happened on this specific page
                        $jobData = $job->variable_data ?? [];
                        $templatesUsed = $job->templates ?? [];

                        // Logic: If variable_data has a specific entry for this page, 
                        // we need to determine which template it belongs to.

                        // If your JSON structure doesn't explicitly store the Template ID 
                        // inside the variable_data, we fallback to the sequence in 'templates'
                        $targetId = $templatesUsed[$pageNumber - 1] ?? null;

                        if (!$targetId) {
                            // Fallback: If 'templates' only has 3 items but 5 pages were printed,
                            // it means the templates array is not a 1:1 mapping.
                            // We then assume the last template is used for all remaining serials.
                            $targetId = end($templatesUsed);
                        }

                        if (!$targetId) return 'N/A';

                        // 3. Fetch from Cache
                        if (!isset($templateCache[$targetId])) {
                            $templateCache[$targetId] = \App\Models\LetterheadTemplate::find($targetId)?->name ?? "Template #{$targetId}";
                        }

                        return $templateCache[$targetId];
                    }),
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
                Action::make('viewDocument')
                    ->label('View Scan')
                    ->icon('heroicon-o-eye')
                    ->iconSize('sm') // Add this to control icon size
                    ->color('gray')
                    ->visible(fn($record): bool => !empty($record->scanned_copy))
                    ->modalHeading('View Uploaded Document')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function ($record) {
                        $fileExtension = pathinfo($record->scanned_copy, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $isPDF = strtolower($fileExtension) === 'pdf';
                        $fileUrl = Storage::url($record->scanned_copy);

                        return view('filament.components.document-preview-modal', [
                            'fileUrl' => $fileUrl,
                            'isImage' => $isImage,
                            'isPDF' => $isPDF,
                            'fileName' => basename($record->scanned_copy),
                        ]);
                    }),
                Action::make('preview_template')
                    ->label('Preview Template')
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
                Action::make('uploadScan')
                    ->label('Upload Scan')
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->color('success')
                    ->visible(fn($record): bool => is_null($record->scanned_copy))
                    ->schema([
                        Forms\Components\FileUpload::make('scanned_copy')
                            ->label('Upload Scan')
                            ->directory('serial-scans')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'scanned_copy' => $data['scanned_copy']
                        ]);
                    }),

                Actions\EditAction::make()
                    ->label('Update Record')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn($record): bool => empty($record->notes)) // Only visible when notes is empty
                    ->schema(fn($record) => [
                        Forms\Components\FileUpload::make('scanned_copy')
                            ->label('Upload Scanned Copy')
                            ->directory('serial-scans')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->required()
                            ->helperText('Upload scanned copy (PDF, JPG, PNG, max 5MB)'),

                        Forms\Components\Select::make('notes')
                            ->label('Print Status')
                            ->options([
                                'successful' => 'Print Successful',
                                'wasted' => 'Wasted',
                            ])
                            ->required()
                            ->helperText('Select the print status. Once set, this record cannot be edited further.'),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->numeric()
                            ->visible(fn() => $record->canEditSerial())
                            ->rules(['integer'])
                            ->helperText("If you need to adjust the serial, update it here.")
                            ->disabled(fn($record): bool => !empty($record->notes)),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update($data);
                    }),

                Actions\DeleteAction::make()->visible(fn($record) => $record->canEditSerial()),
            ])
            // ->toolbarActions([
            //     Actions\BulkActionGroup::make([
            //         Actions\DeleteBulkAction::make(),

            //         Actions\BulkAction::make('upload_scans')
            //             ->label('Upload Scans')
            //             ->icon('heroicon-o-cloud-arrow-up')
            //             ->color('primary')
            //             ->schema([
            //                 Forms\Components\FileUpload::make('scanned_copies')
            //                     ->label('Scanned Copies')
            //                     ->multiple()
            //                     ->directory('serial-scans')
            //                     ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
            //                     ->maxSize(5120)
            //                     ->required(),
            //             ])
            //             ->action(function ($records, array $data) {
            //                 // This would need custom implementation for bulk upload
            //             }),
            //     ]),
            // ])
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

    public static function getNavigationBadge(): ?string
    {
        $count = SerialUsage::whereNull('scanned_copy')->count();
        return $count > 0 ? (string)$count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
