<?php

// app/Filament/Pages/BulkPrintPreview.php

namespace App\Filament\Pages;

use App\Models\LetterheadTemplate;
use App\Models\PrintJob;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class BulkPrintPreview extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document';

    protected string $view = 'filament.pages.bulk-print-preview';

    protected static ?string $title = 'Print Preview';

    protected static bool $shouldRegisterNavigation = false;

    public $printData = [];
    public $templates = [];
    public $renderedContent = [];
    public $printJobId = null;
    public $quantities = [];
    public $startSerials = [];
    public $endSerials = [];

    public function mount()
    {
        $this->printData = session('bulk_print_data', []);
        $templateIds = session('bulk_print_templates', []);
        $this->printJobId = session('print_job_id');
        $this->renderedContent = session('bulk_print_rendered_content', []);

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
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Print All')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->action('printDocument'),
            
            Action::make('back')
                ->label('Back to Print Setup')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(route('filament.admin.pages.bulk-print'))
                ->visible(fn () => !$this->printJobId),
            
            Action::make('done')
                ->label('Done')
                ->icon('heroicon-o-check')
                ->color('success')
                ->url(route('filament.admin.resources.letterhead-templates.index'))
                ->visible(fn () => $this->printJobId),
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