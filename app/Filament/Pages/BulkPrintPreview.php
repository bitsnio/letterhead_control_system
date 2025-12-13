<?php

namespace App\Filament\Pages;

use App\Models\LetterheadTemplate;
use App\Models\PrintJob;
use App\Models\LetterheadInventory as Letterhead;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
    public $orientations = [];
    public $fontSizes = [];
    public $printJobId = null;
    public $quantities = [];
    public $startSerials = [];
    public $endSerials = [];
    public $segmentData = [];
    public $pageCounts = [];

    public function mount()
    {
        $this->printData = session('bulk_print_data', []);
        $templateIds = session('bulk_print_templates', []);
        $this->printJobId = session('print_job_id');
        $this->renderedContent = session('bulk_print_rendered_content', []);
        $this->margins = session('bulk_print_margins', []);
        $this->orientations = session('bulk_print_orientations', []);
        $this->fontSizes = session('bulk_print_font_sizes', []);
        $this->segmentData = session('bulk_print_segment_data', []);
        $this->pageCounts = session('bulk_print_page_counts', []);

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

        // Initialize margins, orientations, and font sizes from print_margins array
        foreach ($this->templates as $template) {
            $printMargins = $template->print_margins ?? [];

            // Initialize margins from print_margins array
            if (!isset($this->margins[$template->id])) {
                $this->margins[$template->id] = [
                    'top' => $printMargins['top'] ?? '15',
                    'right' => $printMargins['right'] ?? '15',
                    'bottom' => $printMargins['bottom'] ?? '15',
                    'left' => $printMargins['left'] ?? '15'
                ];
            }

            // Initialize orientation from print_margins array
            if (!isset($this->orientations[$template->id])) {
                $this->orientations[$template->id] = $printMargins['orientation'] ?? 'portrait';
            }

            // Initialize font size from print_margins array
            if (!isset($this->fontSizes[$template->id])) {
                $this->fontSizes[$template->id] = $printMargins['font_size'] ?? 100;
            }
        }
    }

    // ADD THIS METHOD TO HANDLE PAGE SEGMENTATION
    public function finalizeSegmentsAndSerials(array $segmentData): void
    {
        // Store page counts in session
        session(['bulk_print_page_counts' => $segmentData]);
        $this->pageCounts = $segmentData;
        
        // Now calculate and assign serials based on page counts
        $data = $this->printData;
        $globalLetterheadId = $data['global_letterhead_id'] ?? null;
        
        if (!$globalLetterheadId) {
            Notification::make()
                ->warning()
                ->title('No Batch Selected')
                ->body('Please select a letterhead batch.')
                ->send();
            return;
        }
        
        $letterhead = Letterhead::find($globalLetterheadId);
        if (!$letterhead) {
            Notification::make()
                ->warning()
                ->title('Invalid Batch')
                ->body('Selected letterhead batch not found.')
                ->send();
            return;
        }
        
        $currentSerial = $letterhead->getNextAvailableSerial() ?? $letterhead->start_serial;
        $totalPages = 0;
        
        // Group segments by template
        $templateSegments = [];
        foreach ($segmentData as $segment) {
            $templateId = $segment['templateId'];
            if (!isset($templateSegments[$templateId])) {
                $templateSegments[$templateId] = 0;
            }
            $templateSegments[$templateId] += $segment['pageCount'];
        }
        
        // Update print data with calculated serials
        foreach ($templateSegments as $templateId => $pageCount) {
            if (isset($data['templates'][$templateId])) {
                $data['templates'][$templateId]['start_serial'] = $currentSerial;
                $data['templates'][$templateId]['end_serial'] = $currentSerial + $pageCount - 1;
                $data['templates'][$templateId]['quantity'] = $pageCount; // Set quantity = page count
                
                // Update component properties
                $this->startSerials[$templateId] = $currentSerial;
                $this->endSerials[$templateId] = $currentSerial + $pageCount - 1;
                $this->quantities[$templateId] = $pageCount;
                
                $currentSerial += $pageCount;
                $totalPages += $pageCount;
            }
        }
        
        // Update session with calculated serials
        session(['bulk_print_data' => $data]);
        $this->printData = $data;
        
        Notification::make()
            ->success()
            ->title('Pages Calculated')
            ->body("Total pages needed: {$totalPages}. Serials have been assigned.")
            ->send();
    }

    public function getPrintPagesData(): array
    {
        $pagesData = [];
        
        foreach ($this->templates as $template) {
            if (!isset($this->renderedContent[$template->id])) {
                continue;
            }
            
            // Get page count from calculated data
            $pageCount = 1;
            foreach ($this->pageCounts as $segment) {
                if (isset($segment['templateId']) && $segment['templateId'] == $template->id) {
                    $pageCount = $segment['pageCount'] ?? 1;
                    break;
                }
            }
            
            $margins = $this->getTemplateMargins($template->id);
            $orientation = $this->getTemplateOrientation($template->id);
            $fontSize = $this->getTemplateFontSize($template->id);
            
            $pageWidth = $orientation === 'landscape' ? 297 : 210;
            $pageHeight = $orientation === 'landscape' ? 210 : 297;
            $contentWidth = $pageWidth - $margins['left'] - $margins['right'];
            $contentHeight = $pageHeight - $margins['top'] - $margins['bottom'];
            
            $pagesData[] = [
                'templateId' => $template->id,
                'templateName' => $template->name,
                'content' => $this->renderedContent[$template->id],
                'pageCount' => $pageCount,
                'margins' => $margins,
                'orientation' => $orientation,
                'fontSize' => $fontSize,
                'pageWidth' => $pageWidth,
                'pageHeight' => $pageHeight,
                'contentWidth' => $contentWidth,
                'contentHeight' => $contentHeight,
            ];
        }
        
        return $pagesData;
    }

    public function updatedMargins($value, $key): void
    {
        // When margins change, update session
        session(['bulk_print_margins' => $this->margins]);
        $this->dispatch('content-updated');
    }

    public function updatedOrientations($value, $key): void
    {
        // When orientation changes, update session
        session(['bulk_print_orientations' => $this->orientations]);
        $this->dispatch('content-updated');
    }

    public function updatedFontSizes($value, $key): void
    {
        // When font size changes, update session
        session(['bulk_print_font_sizes' => $this->fontSizes]);
        $this->dispatch('content-updated');
    }

    public function updateMargin($templateId, $side, $value): void
    {
        $this->margins[$templateId][$side] = $value;
        session(['bulk_print_margins' => $this->margins]);
        $this->dispatch('content-updated');
    }

    public function updateOrientation($templateId, $value): void
    {
        $this->orientations[$templateId] = $value;
        session(['bulk_print_orientations' => $this->orientations]);
        $this->dispatch('content-updated');
    }

    public function updateFontSize($templateId, $value): void
    {
        $this->fontSizes[$templateId] = $value;
        session(['bulk_print_font_sizes' => $this->fontSizes]);
        $this->dispatch('content-updated');
    }

    public function saveMargins(): void
    {
        try {
            // Update session with current settings before saving
            session([
                'bulk_print_margins' => $this->margins,
                'bulk_print_orientations' => $this->orientations,
                'bulk_print_font_sizes' => $this->fontSizes,
            ]);

            DB::beginTransaction();

            $savedCount = 0;
            foreach ($this->templates as $template) {
                $updateData = [];

                // Get existing print_margins or initialize empty array
                $existingMargins = $template->print_margins ?? [];

                // Build updated print_margins array
                $updatedMargins = array_merge($existingMargins, [
                    'top' => $this->margins[$template->id]['top'] ?? ($existingMargins['top'] ?? '15'),
                    'right' => $this->margins[$template->id]['right'] ?? ($existingMargins['right'] ?? '15'),
                    'bottom' => $this->margins[$template->id]['bottom'] ?? ($existingMargins['bottom'] ?? '15'),
                    'left' => $this->margins[$template->id]['left'] ?? ($existingMargins['left'] ?? '15'),
                    'orientation' => $this->orientations[$template->id] ?? ($existingMargins['orientation'] ?? 'portrait'),
                    'font_size' => $this->fontSizes[$template->id] ?? ($existingMargins['font_size'] ?? 100),
                ]);

                // Save the combined print_margins array
                $updateData['print_margins'] = $updatedMargins;

                if (!empty($updateData)) {
                    $template->update($updateData);
                    $savedCount++;
                }
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Settings Saved')
                ->body("Print settings (margins, orientation, font size) have been saved to {$savedCount} template(s) for future use.")
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

            // Prepare margins array with all settings for each template
            $marginsWithSettings = [];
            foreach ($this->templates as $template) {
                $marginsWithSettings[$template->id] = [
                    'top' => $this->margins[$template->id]['top'] ?? '15',
                    'right' => $this->margins[$template->id]['right'] ?? '15',
                    'bottom' => $this->margins[$template->id]['bottom'] ?? '15',
                    'left' => $this->margins[$template->id]['left'] ?? '15',
                    'orientation' => $this->orientations[$template->id] ?? 'portrait',
                    'font_size' => $this->fontSizes[$template->id] ?? 100,
                ];
            }

            // Calculate total pages from page counts
            $totalPages = 0;
            foreach ($this->pageCounts as $segment) {
                $totalPages += $segment['pageCount'] ?? 1;
            }

            // Create a single print job for all templates
            $printJob = PrintJob::create([
                'user_id' => auth()->id(),
                'templates' => $this->templates->pluck('id')->toArray(),
                'variable_data' => collect($data['templates'])->mapWithKeys(function ($templateData, $templateId) {
                    return [$templateId => $templateData['variable_data']];
                })->toArray(),
                'margins' => $marginsWithSettings,
                'quantity' => $totalPages,
                'start_serial' => collect($data['templates'])->min('start_serial'),
                'end_serial' => collect($data['templates'])->max('end_serial'),
                'letterhead_id' => $globalLetterheadId,
                'status' => 'completed',
            ]);

            // Allocate serials with template assignments
            $allocated = $letterhead->allocateSerialsWithTemplates($printJob, $data['templates']);

            if (!$allocated) {
                throw new \Exception("Failed to allocate serials for the print job.");
            }

            DB::commit();

            session(['print_job_id' => $printJob->id]);
            $this->printJobId = $printJob->id;

            Notification::make()
                ->success()
                ->title('Print Job Created')
                ->body('Print job has been marked as completed. You can now upload scanned copies against this job.')
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

            $quantity = (int) ($templateData['quantity'] ?? 0);
            if ($quantity < 1) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' requires at least 1 page."
                ];
            }

            $totalQuantity += $quantity;
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

            $expectedEndSerial = $startSerial + $quantity - 1;
            if ($endSerial !== $expectedEndSerial) {
                return [
                    'success' => false,
                    'message' => "Template '{$templateName}' serial range should be {$startSerial}-{$expectedEndSerial}"
                ];
            }

            $previousEndSerial = $endSerial;
        }

        $firstTemplate = reset($data['templates']);
        $lastTemplate = end($data['templates']);
        $totalStartSerial = $firstTemplate['start_serial'] ?? 0;
        $totalEndSerial = $lastTemplate['end_serial'] ?? 0;

        if ($totalStartSerial < $letterhead->start_serial || $totalEndSerial > $letterhead->end_serial) {
            return [
                'success' => false,
                'message' => "Total serial range {$totalStartSerial}-{$totalEndSerial} exceeds batch range"
            ];
        }

        $errors = $letterhead->validateSerialRange($totalStartSerial, $totalEndSerial);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode('. ', $errors)];
        }

        return ['success' => true];
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

    public function getTemplateOrientation($templateId): string
    {
        return $this->orientations[$templateId] ?? 'portrait';
    }

    public function getTemplateFontSize($templateId): int
    {
        return $this->fontSizes[$templateId] ?? 100;
    }

    public function getTemplateSerialRange($templateId): string
    {
        $start = $this->startSerials[$templateId] ?? null;
        $end = $this->endSerials[$templateId] ?? null;

        if ($start && $end) {
            return $start === $end ? (string) $start : $start . ' - ' . $end;
        }

        return 'Not set';
    }

    public function getTemplateQuantity($templateId): int
    {
        return $this->quantities[$templateId] ?? 1;
    }

    public function getSerialInfo(): array
    {
        $totalQuantity = array_sum($this->quantities);
        $allStartSerials = array_filter($this->startSerials);
        $allEndSerials = array_filter($this->endSerials);

        if (empty($allStartSerials) || empty($allEndSerials)) {
            return [
                'quantity' => $totalQuantity,
                'serial_display' => 'Not calculated yet'
            ];
        }

        $startSerial = min($allStartSerials);
        $endSerial = max($allEndSerials);

        return [
            'quantity' => $totalQuantity,
            'serial_display' => $startSerial . ' - ' . $endSerial
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
                ->label('Save Settings')
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
}