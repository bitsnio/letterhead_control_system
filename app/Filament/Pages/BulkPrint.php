<?php

namespace App\Filament\Pages;

use App\Models\LetterheadTemplate;
use App\Models\PrintJob;
use App\Models\LetterheadInventory as Letterhead;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor; // <-- Import RichEditor
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Infolists\Components\RepeatableEntry;

class BulkPrint extends Page
{
    use \Filament\Forms\Concerns\InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-printer';
    protected static ?string $title = 'Bulk Print Templates';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.bulk-print';

    public ?array $data = [];
    public $templates;

    public function mount(): void
    {
        $templateIds = session('selected_templates_for_print', []);

        if (empty($templateIds)) {
            Notification::make()
                ->warning()
                ->title('No Templates Selected')
                ->body('Please select templates from the list first.')
                ->send();

            $this->redirect(route('filament.admin.resources.letterhead-templates.index'));
            return;
        }

        $this->templates = LetterheadTemplate::query()
            ->whereIn('id', $templateIds)
            ->where('approval_status', 'approved')
            ->where('is_active', true)
            ->get();

        if ($this->templates->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('No Valid Templates')
                ->body('Selected templates are not available for printing.')
                ->send();

            $this->redirect(route('filament.admin.resources.letterhead-templates.index'));
            return;
        }

        // Prepare initial form data for each template
        $initialData = [];

        foreach ($this->templates as $template) {
            $initialData['templates'][$template->id] = [
                'quantity' => 1,
                'letterhead_id' => null,
                'start_serial' => null,
                'end_serial' => null,
                'variable_data' => [],
            ];

            foreach ($template->variables ?? [] as $variable) {
                $initialData['templates'][$template->id]['variable_data'][$variable] = '';
            }
        }

        $this->form->fill($initialData);
    }


    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Letterhead Batch Selection')
                    ->schema([
                        Select::make('global_letterhead_id')
                            ->label('Select Letterhead Batch')
                            ->options(
                                Letterhead::query()
                                    ->get()
                                    ->mapWithKeys(fn($l) => [
                                        $l->id => "{$l->batch_name} (Range: {$l->start_serial}-{$l->end_serial}, Available: " . count($l->availableSerials) . ")"
                                    ])
                                    ->toArray()
                            )
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) return;

                                $letterhead = Letterhead::find($state);
                                if ($letterhead) {
                                    // Just set the batch, don't pre-calculate serials
                                    $currentData = $get();
                                    foreach ($this->templates as $template) {
                                        $set("templates.{$template->id}.letterhead_id", $state);
                                        // Don't set start/end serials yet - will be calculated after preview
                                        $set("templates.{$template->id}.start_serial", null);
                                        $set("templates.{$template->id}.end_serial", null);
                                    }
                                }
                            }),
                    ]),

                ...$this->getTemplateFields()
            ])
            ->statePath('data');
    }
    // public function form(Schema $form): Schema
    // {
    //     return $form
    //         ->schema([
    //             Section::make('Letterhead Batch Selection')
    //                 ->schema([
    //                     Select::make('global_letterhead_id')
    //                         ->label('Select Letterhead Batch')
    //                         ->options(
    //                             Letterhead::query()
    //                                 ->get()
    //                                 ->mapWithKeys(fn($l) => [
    //                                     $l->id => "{$l->batch_name} (Range: {$l->start_serial}-{$l->end_serial}, Available: " . count($l->availableSerials) . ")"
    //                                 ])
    //                                 ->toArray()
    //                         )
    //                         ->searchable()
    //                         ->required()
    //                         ->reactive()
    //                         ->afterStateUpdated(function ($state, callable $set, callable $get) {
    //                             if (!$state) return;

    //                             $letterhead = Letterhead::find($state);
    //                             if ($letterhead) {
    //                                 // Reset all template serials and set the same batch for all
    //                                 $currentData = $get();
    //                                 $startSerial = $letterhead->getNextAvailableSerial() ?? $letterhead->start_serial;

    //                                 foreach ($this->templates as $template) {
    //                                     $quantity = $currentData['templates'][$template->id]['quantity'] ?? 1;
    //                                     $set("templates.{$template->id}.letterhead_id", $state);
    //                                     $set("templates.{$template->id}.start_serial", $startSerial);
    //                                     $set("templates.{$template->id}.end_serial", $startSerial + $quantity - 1);
    //                                     $startSerial += $quantity;
    //                                 }
    //                             }
    //                         }),
    //                 ]),

    //             ...$this->getTemplateFields()
    //         ])
    //         ->statePath('data');
    // }

    // protected function getTemplateFields(): array
    // {
    //     $fields = [];

    //     foreach ($this->templates as $template) {

    //         $fields[] = Fieldset::make($template->name)
    //             ->schema([
    //                 // Quantity Field
    //                 TextInput::make("templates.{$template->id}.quantity")
    //                     ->label('Print Quantity')
    //                     ->numeric()
    //                     ->required()
    //                     ->default(1)
    //                     ->minValue(1)
    //                     ->reactive()
    //                     ->afterStateUpdated(function ($state, callable $set, callable $get) {
    //                         $globalLetterheadId = $get('global_letterhead_id');
    //                         if ($globalLetterheadId && $state > 0) {
    //                             $this->recalculateAllSerials($set, $get);
    //                         }
    //                     }),

    //                 // Hidden batch ID
    //                 TextInput::make("templates.{$template->id}.letterhead_id")
    //                     ->hidden()
    //                     ->dehydrated(),

    //                 // Serial Range (flat, no inner section)
    //                 Grid::make(2)->schema([
    //                     TextInput::make("templates.{$template->id}.start_serial")
    //                         ->label('Start Serial')
    //                         ->numeric()
    //                         ->disabled()
    //                         ->dehydrated(),

    //                     TextInput::make("templates.{$template->id}.end_serial")
    //                         ->label('End Serial')
    //                         ->numeric()
    //                         ->disabled()
    //                         ->dehydrated(),
    //                 ]),

    //                 // Variable Fields
    //                 ...$this->getVariableFieldsForTemplate($template),
    //             ])
    //             ->columns(1);
    //     }

    //     return $fields;
    // }

    protected function getTemplateFields(): array
    {
        $fields = [];

        foreach ($this->templates as $template) {
            $fields[] = Fieldset::make($template->name)
                ->schema([
                    // Remove quantity field - will be calculated dynamically

                    // Hidden batch ID
                    TextInput::make("templates.{$template->id}.letterhead_id")
                        ->hidden()
                        ->dehydrated(),

                    // Serial Range - will be filled after preview
                    Grid::make(2)->schema([
                        TextInput::make("templates.{$template->id}.start_serial")
                            ->label('Start Serial')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Will be calculated after preview'),

                        TextInput::make("templates.{$template->id}.end_serial")
                            ->label('End Serial')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Will be calculated after preview'),
                    ]),

                    // Variable Fields
                    ...$this->getVariableFieldsForTemplate($template),
                ])
                ->columns(1);
        }

        return $fields;
    }

    protected function recalculateAllSerials(callable $set, callable $get): void
    {
        $globalLetterheadId = $get('global_letterhead_id');
        if (!$globalLetterheadId) return;

        $letterhead = Letterhead::find($globalLetterheadId);
        if (!$letterhead) return;

        $currentData = $get();
        $startSerial = $letterhead->getNextAvailableSerial() ?? $letterhead->start_serial;

        foreach ($this->templates as $template) {
            $quantity = $currentData['templates'][$template->id]['quantity'] ?? 1;
            $set("templates.{$template->id}.letterhead_id", $globalLetterheadId);
            $set("templates.{$template->id}.start_serial", $startSerial);
            $set("templates.{$template->id}.end_serial", $startSerial + $quantity - 1);
            $startSerial += $quantity;
        }
    }

    /**
     * Dynamically creates form fields (TextInput or RichEditor) for template variables.
     * * @param mixed $template
     * @return array
     */
    protected function getVariableFieldsForTemplate($template): array
    {
        $fields = [];

        if (!empty($template->variables)) {
            $variableFields = collect($template->variables)->map(function ($var) use ($template) {

                // Check if the variable is designated for rich content (case-insensitive)
                if (strtoupper($var) === '$TABLE$' || strtoupper($var) === 'TABLE') {
                    // Use RichEditor for table variables
                    return RichEditor::make("templates.{$template->id}.variable_data.{$var}")
                        ->label("Table/Rich Content for {$var}")
                        ->required()
                        ->placeholder("Enter styled content or table for {$var}")
                        ->columnSpanFull() // Make rich editor span full width
                        ->toolbarButtons([ // Optional: Customize toolbar if needed
                            'bold',
                            'italic',
                            'strike',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                            'table', // Keep table button
                        ]);
                } else {
                    // Use standard TextInput for all other variables
                    return TextInput::make("templates.{$template->id}.variable_data.{$var}")
                        ->label("{$var}")
                        ->required()
                        ->placeholder("Enter value for {$var}");
                }
            })->toArray();

            $fields[] = Section::make('Template Variables')
                ->schema($variableFields)
                ->columns(2) // Columns(2) for text inputs, but RichEditor will use columnSpanFull
                ->collapsed(false);
        }

        return $fields;
    }

    // public function preview(): void
    // {
    //     $data = $this->form->getState();
    //     $validation = $this->validateAllTemplates($data);

    //     if (!$validation['success']) {
    //         Notification::make()
    //             ->danger()
    //             ->title('Validation Error')
    //             ->body($validation['message'])
    //             ->send();
    //         return;
    //     }
    //     // Process template content with variable replacement
    //     $renderedContent = [];
    //     foreach ($this->templates as $template) {
    //         $templateData = $data['templates'][$template->id] ?? [];
    //         $variableData = $templateData['variable_data'] ?? [];

    //         // Get template content (adjust this based on your template model structure)
    //         $content = $template->content ?? ''; // Assuming 'content' field exists

    //         // Replace variables in the content
    //         $renderedContent[$template->id] = $this->replaceVariables($content, $variableData);
    //     }

    //     session([
    //         'bulk_print_data' => $data,
    //         'bulk_print_templates' => $this->templates->pluck('id')->toArray(),
    //         'bulk_print_rendered_content' => $renderedContent, // Add rendered content to session
    //     ]);

    //     $this->redirect(route('filament.admin.pages.bulk-print-preview'));
    // }

    public function preview(): void
    {
        $data = $this->form->getState();

        // Basic validation (no quantity/range validation yet)
        if (!isset($data['global_letterhead_id'])) {
            Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body('Please select a letterhead batch.')
                ->send();
            return;
        }

        // Process template content with variable replacement
        $renderedContent = [];
        $segmentData = []; // This will store page segmentation data

        foreach ($this->templates as $template) {
            $templateData = $data['templates'][$template->id] ?? [];
            $variableData = $templateData['variable_data'] ?? [];

            $content = $template->content ?? '';
            $renderedContent[$template->id] = $this->replaceVariables($content, $variableData);

            // Store template data for later use
            $segmentData[$template->id] = [
                'variable_data' => $variableData,
                'content' => $renderedContent[$template->id]
            ];
        }

        session([
            'bulk_print_data' => $data,
            'bulk_print_templates' => $this->templates->pluck('id')->toArray(),
            'bulk_print_rendered_content' => $renderedContent,
            'bulk_print_segment_data' => $segmentData, // Add segmentation data
            'bulk_print_page_counts' => [], // Will be filled by JavaScript calculation
        ]);

        $this->redirect(route('filament.admin.pages.bulk-print-preview'));
    }
    /**
     * Replace variables in template content
     */
    // In App\Filament\Pages\BulkPrint
    protected function replaceVariables(string $content, array $variableData): string
    {
        foreach ($variableData as $variable => $value) {

            // Check if this is the TABLE variable (case-insensitive)
            $isTableVariable = strtoupper($variable) === 'TABLE';

            // If it is the table variable, wrap the value in a unique class
            if ($isTableVariable) {
                // Apply the unique class wrapper
                $value = '<div class="table-variable-content">' . $value . '</div>';
            }

            // Replace multiple variable formats
            $content = str_replace(
                [
                    '$' . $variable . '$',
                    '{' . $variable . '}',
                    '{{' . $variable . '}}',
                    '{$' . $variable . '$}'
                ],
                $value,
                $content
            );
        }

        return $content;
    }

    public function print(): void
    {
        $data = $this->form->getState();
        $validation = $this->validateAllTemplates($data);

        if (!$validation['success']) {
            Notification::make()
                ->danger()
                ->title('Validation Error')
                ->body($validation['message'])
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            $globalLetterheadId = $data['global_letterhead_id'];
            $letterhead = Letterhead::findOrFail($globalLetterheadId);

            // Create a single print job for all templates
            $printJob = PrintJob::create([
                'user_id' => auth()->id(),
                'templates' => $this->templates->pluck('id')->toArray(),
                'variable_data' => collect($data['templates'])->mapWithKeys(function ($templateData, $templateId) {
                    return [$templateId => $templateData['variable_data']];
                })->toArray(),
                'quantity' => collect($data['templates'])->sum('quantity'),
                'start_serial' => collect($data['templates'])->min('start_serial'),
                'end_serial' => collect($data['templates'])->max('end_serial'),
                'letterhead_id' => $globalLetterheadId,
                'status' => 'pending',
            ]);

            // Allocate serials for the entire range
            $totalStartSerial = collect($data['templates'])->min('start_serial');
            $totalEndSerial = collect($data['templates'])->max('end_serial');

            $allocated = $letterhead->allocateSerials($printJob, $totalStartSerial, $totalEndSerial);

            if (!$allocated) {
                throw new \Exception("Failed to allocate serials for the print job.");
            }

            $printJob->markAsCompleted();

            DB::commit();

            // Also process content for preview even when printing directly
            $renderedContent = [];
            foreach ($this->templates as $template) {
                $templateData = $data['templates'][$template->id] ?? [];
                $variableData = $templateData['variable_data'] ?? [];
                $content = $template->content ?? '';
                $renderedContent[$template->id] = $this->replaceVariables($content, $variableData);
            }

            session([
                'bulk_print_data' => $data,
                'bulk_print_templates' => $this->templates->pluck('id')->toArray(),
                'bulk_print_rendered_content' => $renderedContent,
                'print_job_id' => $printJob->id,
            ]);

            Notification::make()
                ->success()
                ->title('Print Job Created')
                ->body('All templates processed successfully with consecutive serial numbers.')
                ->send();

            $this->redirect(route('filament.admin.pages.bulk-print-preview'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Print Failed')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    // protected function validateAllTemplates(array $data): array
    // {
    //     if (!isset($data['templates']) || !isset($data['global_letterhead_id'])) {
    //         return ['success' => false, 'message' => 'Please select a letterhead batch.'];
    //     }

    //     $globalLetterheadId = $data['global_letterhead_id'];
    //     $letterhead = Letterhead::find($globalLetterheadId);

    //     if (!$letterhead) {
    //         return ['success' => false, 'message' => 'Invalid letterhead selected'];
    //     }

    //     $previousEndSerial = null;
    //     $totalQuantity = 0;

    //     foreach ($data['templates'] as $templateId => $templateData) {
    //         $templateName = $this->templates->firstWhere('id', $templateId)->name ?? 'Unknown Template';

    //         // Validate quantity
    //         $quantity = (int) $templateData['quantity'];
    //         if ($quantity < 1) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Template '{$templateName}' must have a quantity of at least 1."
    //             ];
    //         }

    //         $totalQuantity += $quantity;

    //         // Validate serial continuity
    //         $startSerial = (int) $templateData['start_serial'];
    //         $endSerial = (int) $templateData['end_serial'];

    //         if ($previousEndSerial !== null && $startSerial !== $previousEndSerial + 1) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Template '{$templateName}' should start from serial " . ($previousEndSerial + 1) . " (continuing from previous template)"
    //             ];
    //         }

    //         // Validate serial range matches quantity
    //         $expectedEndSerial = $startSerial + $quantity - 1;
    //         if ($endSerial !== $expectedEndSerial) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Template '{$templateName}' serial range should be {$startSerial}-{$expectedEndSerial} for quantity {$quantity}"
    //             ];
    //         }

    //         $previousEndSerial = $endSerial;
    //     }

    //     // Validate total range fits in batch
    //     $firstTemplate = reset($data['templates']);
    //     $lastTemplate = end($data['templates']);

    //     $totalStartSerial = $firstTemplate['start_serial'];
    //     $totalEndSerial = $lastTemplate['end_serial'];

    //     if ($totalStartSerial < $letterhead->start_serial || $totalEndSerial > $letterhead->end_serial) {
    //         return [
    //             'success' => false,
    //             'message' => "Total serial range {$totalStartSerial}-{$totalEndSerial} exceeds batch range {$letterhead->start_serial}-{$letterhead->end_serial}"
    //         ];
    //     }

    //     // Use the model's validateSerialRange method
    //     $errors = $letterhead->validateSerialRange($totalStartSerial, $totalEndSerial);
    //     if (!empty($errors)) {
    //         return [
    //             'success' => false,
    //             'message' => implode('. ', $errors)
    //         ];
    //     }

    //     return ['success' => true];
    // }

    // In BulkPrint.php - Update validateAllTemplates method
    protected function validateAllTemplates(array $data): array
    {
        if (!isset($data['templates']) || !isset($data['global_letterhead_id'])) {
            return ['success' => false, 'message' => 'Please select a letterhead batch.'];
        }

        $globalLetterheadId = $data['global_letterhead_id'];
        $letterhead = Letterhead::find($globalLetterheadId);

        if (!$letterhead) {
            return ['success' => false, 'message' => 'Invalid letterhead selected'];
        }

        $previousEndSerial = null;
        $totalQuantity = 0;

        foreach ($data['templates'] as $templateId => $templateData) {
            $templateName = $this->templates->firstWhere('id', $templateId)->name ?? 'Unknown Template';

            // Get quantity from page count (not from user input)
            $quantity = (int) ($templateData['quantity'] ?? 0);
            if ($quantity < 1) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' requires at least 1 page."
                ];
            }

            $totalQuantity += $quantity;

            // Validate serial continuity
            $startSerial = (int) ($templateData['start_serial'] ?? 0);
            $endSerial = (int) ($templateData['end_serial'] ?? 0);

            if ($startSerial === 0 || $endSerial === 0) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' serials not calculated. Please preview first."
                ];
            }

            if ($previousEndSerial !== null && $startSerial !== $previousEndSerial + 1) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' should start from serial " . ($previousEndSerial + 1)
                ];
            }

            // Validate serial range matches calculated page count
            $expectedEndSerial = $startSerial + $quantity - 1;
            if ($endSerial !== $expectedEndSerial) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' serial range should be {$startSerial}-{$expectedEndSerial} for {$quantity} pages"
                ];
            }

            $previousEndSerial = $endSerial;
        }

        // Validate total range fits in batch
        $firstTemplate = reset($data['templates']);
        $lastTemplate = end($data['templates']);

        $totalStartSerial = $firstTemplate['start_serial'] ?? 0;
        $totalEndSerial = $lastTemplate['end_serial'] ?? 0;

        if ($totalStartSerial < $letterhead->start_serial || $totalEndSerial > $letterhead->end_serial) {
            return [
                'success' => false,
                'message' => "Total serial range {$totalStartSerial}-{$totalEndSerial} exceeds batch range {$letterhead->start_serial}-{$letterhead->end_serial}"
            ];
        }

        // Use the model's validateSerialRange method
        $errors = $letterhead->validateSerialRange($totalStartSerial, $totalEndSerial);
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('. ', $errors)
            ];
        }

        return ['success' => true];
    }
    // Inside App\Filament\Pages\BulkPrint.php

    protected function getTemplateSettings($template): array
    {
        // Retrieve print_margins array from the template model (where settings are saved)
        $printMargins = $template->print_margins ?? [];

        // Default values if settings aren't set
        return [
            'orientation' => $printMargins['orientation'] ?? 'portrait',
            'margin_top' => $printMargins['top'] ?? '15',
            'margin_right' => $printMargins['right'] ?? '15',
            'margin_bottom' => $printMargins['bottom'] ?? '15',
            'margin_left' => $printMargins['left'] ?? '15',
            'font_size' => $printMargins['font_size'] ?? 100,
        ];
    }

    public function finalizeSegmentsAndSerials(array $segmentData): void
    {
        // Store page counts in session
        session(['bulk_print_page_counts' => $segmentData]);

        // Now calculate and assign serials based on page counts
        $data = session('bulk_print_data', []);
        $globalLetterheadId = $data['global_letterhead_id'] ?? null;

        if (!$globalLetterheadId) return;

        $letterhead = Letterhead::find($globalLetterheadId);
        if (!$letterhead) return;

        $currentSerial = $letterhead->getNextAvailableSerial() ?? $letterhead->start_serial;
        $totalPages = 0;

        foreach ($segmentData as $segment) {
            $templateId = $segment['templateId'];
            $pageCount = $segment['pageCount'];

            // Update the form data with calculated serials
            if (isset($data['templates'][$templateId])) {
                $data['templates'][$templateId]['start_serial'] = $currentSerial;
                $data['templates'][$templateId]['end_serial'] = $currentSerial + $pageCount - 1;
                $data['templates'][$templateId]['quantity'] = $pageCount; // Set quantity = page count

                $currentSerial += $pageCount;
                $totalPages += $pageCount;
            }
        }

        // Update session with calculated serials
        session(['bulk_print_data' => $data]);

        // Also update the form state
        $this->form->fill($data);

        Notification::make()
            ->success()
            ->title('Pages Calculated')
            ->body("Total pages needed: {$totalPages}. Serials have been assigned.")
            ->send();
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->action('preview'),

            Action::make('print')
                ->label('Print Now')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->action('print'),

            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->url(route('filament.admin.resources.letterhead-templates.index')),
        ];
    }
}
