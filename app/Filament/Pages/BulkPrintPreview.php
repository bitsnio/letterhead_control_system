<?php

namespace App\Filament\Pages;

use App\Models\LetterheadTemplate;
use App\Models\PrintJob;
use App\Models\LetterheadInventory as Letterhead;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class BulkPrintPreview extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
    protected string $view = 'filament.pages.bulk-print-preview';
    protected static ?string $title = 'Print Preview';
    protected static bool $shouldRegisterNavigation = false;

    public $printData = [];
    public $templates = [];
    public $renderedContent = [];
    public $margins = [];
    public $printJobId = null;
    public $quantities = [];
    public $startSerials = [];
    public $endSerials = [];

    public ?array $marginData = [];

    public function mount()
    {
        $this->printData = session('bulk_print_data', []);
        $templateIds = session('bulk_print_templates', []);
        $this->printJobId = session('print_job_id');
        $this->renderedContent = session('bulk_print_rendered_content', []);
        $this->margins = session('bulk_print_margins', []);

        if (!$this->printData || !$templateIds) {
            Notification::make()
                ->warning()
                ->title('No Print Data')
                ->body('Please select templates and fill in variables first.')
                ->send();

            return redirect()->route('filament.admin.resources.letterhead-templates.index');
        }

        $this->templates = LetterheadTemplate::whereIn('id', $templateIds)->get();

        // Extract quantities and serials from the print data
        foreach ($this->printData['templates'] ?? [] as $templateId => $templateData) {
            $this->quantities[$templateId] = $templateData['quantity'] ?? 1;
            $this->startSerials[$templateId] = $templateData['start_serial'] ?? null;
            $this->endSerials[$templateId] = $templateData['end_serial'] ?? null;
        }

        // Prepare margin form data
        $this->prepareMarginFormData();
    }

    protected function prepareMarginFormData(): void
    {
        foreach ($this->templates as $template) {
            $templateMargins = $this->margins[$template->id] ?? [
                'top' => '15',
                'right' => '15',
                'bottom' => '15',
                'left' => '15'
            ];

            $this->marginData["template_{$template->id}"] = $templateMargins;
        }
    }

    public function form(Schema $form): Schema
    {
        $schema = [];

        foreach ($this->templates as $template) {
            $schema[] = Section::make("Margins for: {$template->name}")
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make("template_{$template->id}.top")
                            ->label('Top Margin (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateMargins();
                            }),

                        TextInput::make("template_{$template->id}.right")
                            ->label('Right Margin (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateMargins();
                            }),

                        TextInput::make("template_{$template->id}.bottom")
                            ->label('Bottom Margin (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateMargins();
                            }),

                        TextInput::make("template_{$template->id}.left")
                            ->label('Left Margin (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateMargins();
                            }),
                    ]),
                ])
                ->collapsible()
                ->collapsed(true);
        }

        return $form
            ->schema($schema)
            ->statePath('marginData');
    }

    public function updateMargins(): void
    {
        foreach ($this->templates as $template) {
            $marginKey = "template_{$template->id}";
            if (isset($this->marginData[$marginKey])) {
                $this->margins[$template->id] = $this->marginData[$marginKey];
            }
        }

        // Update session with new margins
        session(['bulk_print_margins' => $this->margins]);
    }

    public function saveMargins(): void
    {
        try {
            DB::beginTransaction();

            foreach ($this->templates as $template) {
                $marginKey = "template_{$template->id}";
                if (isset($this->marginData[$marginKey])) {
                    $template->update([
                        'print_margins' => $this->marginData[$marginKey]
                    ]);
                }
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Margins Saved')
                ->body('Print margins have been saved to templates for future use.')
                ->send();
        } catch (\Throwable $e) {
            DB::rollBack();
            Notification::make()
                ->danger()
                ->title('Save Failed')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function markPrintingComplete(): void
    {
        $data = $this->printData;
        
        // Validate the data
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
                'margins' => $this->margins,
                'quantity' => collect($data['templates'])->sum('quantity'),
                'start_serial' => collect($data['templates'])->min('start_serial'),
                'end_serial' => collect($data['templates'])->max('end_serial'),
                'letterhead_id' => $globalLetterheadId,
                'status' => 'completed', // Mark as completed since we're printing
            ]);

            // Allocate serials for the entire range
            $totalStartSerial = collect($data['templates'])->min('start_serial');
            $totalEndSerial = collect($data['templates'])->max('end_serial');

            $allocated = $letterhead->allocateSerials($printJob, $totalStartSerial, $totalEndSerial);

            if (!$allocated) {
                throw new \Exception("Failed to allocate serials for the print job.");
            }

            DB::commit();

            // Update session with print job ID
            session(['print_job_id' => $printJob->id]);
            $this->printJobId = $printJob->id;

            Notification::make()
                ->success()
                ->title('Print Job Created')
                ->body('Print job has been marked as completed. You can now upload scanned copies against this job.')
                ->send();

            // Refresh the page to show the updated state
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

            // Validate quantity
            $quantity = (int) $templateData['quantity'];
            if ($quantity < 1) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' must have a quantity of at least 1."
                ];
            }

            $totalQuantity += $quantity;

            // Validate serial continuity
            $startSerial = (int) $templateData['start_serial'];
            $endSerial = (int) $templateData['end_serial'];

            if ($previousEndSerial !== null && $startSerial !== $previousEndSerial + 1) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' should start from serial " . ($previousEndSerial + 1) . " (continuing from previous template)"
                ];
            }

            // Validate serial range matches quantity
            $expectedEndSerial = $startSerial + $quantity - 1;
            if ($endSerial !== $expectedEndSerial) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' serial range should be {$startSerial}-{$expectedEndSerial} for quantity {$quantity}"
                ];
            }

            $previousEndSerial = $endSerial;
        }

        // Validate total range fits in batch
        $firstTemplate = reset($data['templates']);
        $lastTemplate = end($data['templates']);

        $totalStartSerial = $firstTemplate['start_serial'];
        $totalEndSerial = $lastTemplate['end_serial'];

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

    protected function replaceVariables(string $content, array $variableData): string
    {
        foreach ($variableData as $variable => $value) {
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

    public function getTemplateMargins($templateId): array
    {
        return $this->margins[$templateId] ?? [
            'top' => '15',
            'right' => '15',
            'bottom' => '15',
            'left' => '15'
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_printing_complete')
                ->label('Mark Printing Complete')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action('markPrintingComplete')
                ->visible(fn() => !$this->printJobId),

            Action::make('save_margins')
                ->label('Save Margins')
                ->icon('heroicon-o-bookmark')
                ->color('primary')
                ->action('saveMargins')
                ->visible(fn() => !$this->printJobId),

            Action::make('print')
                ->label('Print All')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->action('printDocument')
                ->visible(fn() => !$this->printJobId),

            Action::make('back')
                ->label('Back to Print Setup')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.pages.bulk-print'))
                ->visible(fn() => !$this->printJobId),

            Action::make('done')
                ->label('Done')
                ->icon('heroicon-o-check')
                ->color('success')
                ->url(route('filament.admin.resources.letterhead-templates.index'))
                ->visible(fn() => $this->printJobId),
        ];
    }

    public function printDocument()
    {
        $this->dispatch('print-document');
    }

    public function getSerialInfo(): array
    {
        $totalQuantity = array_sum($this->quantities);
        $allStartSerials = array_filter($this->startSerials);
        $allEndSerials = array_filter($this->endSerials);

        if (empty($allStartSerials) || empty($allEndSerials)) {
            return [
                'quantity' => $totalQuantity,
                'serial_display' => 'Not set'
            ];
        }

        $startSerial = min($allStartSerials);
        $endSerial = max($allEndSerials);

        return [
            'quantity' => $totalQuantity,
            'serial_display' => $startSerial . ' - ' . $endSerial
        ];
    }

    public function getTemplateQuantity($templateId): int
    {
        return $this->quantities[$templateId] ?? 1;
    }

    public function getTemplateSerialRange($templateId): string
    {
        $start = $this->startSerials[$templateId] ?? null;
        $end = $this->endSerials[$templateId] ?? null;

        if ($start && $end) {
            return $start === $end ? $start : $start . ' - ' . $end;
        }

        return 'Not set';
    }
}