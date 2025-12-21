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
use DOMDocument;
use DOMElement;
use Exception;
use Illuminate\Support\Facades\Log;
use App\CustomClasses\HTMLPageSplitter;

class BulkPrintPreview extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document';
    protected string $view = 'filament.pages.bulk-print-preview';
    protected static ?string $title = 'Print Preview';
    protected static bool $shouldRegisterNavigation = false;
    public bool $isForPrintPreview = false; // Default to false for web preview

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
    // Add these properties to the BulkPrintPreview class
    public array $splitConfig = [
        'mm_to_px_factor' => 3.5, // Conversion factor for mm to pixels
        'line_height_multiplier' => 1.2, // Line height multiplier
        'safety_margin_multiplier' => 1.05, // Safety margin
        'chars_per_line_padding' => 20, // Padding for characters per line
        'min_chars_per_line' => 20,
        'max_chars_per_line' => 120,
        'table_row_multiplier' => 3, // Table row line multiplier
    ];


    public function pageSplitter()
    {

        $content = '<p></p><table><tbody><tr><td rowspan="1" colspan="4" data-colwidth="127,552,405,25"><p style="text-align: justify;">                                                                                                                                      <strong>INVOICE</strong></p></td></tr><tr><td rowspan="1" colspan="1" data-colwidth="127"><p>Invoice No. :</p></td><td rowspan="1" colspan="1" data-colwidth="552"><p>\u{A0}2348729837498</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Date :</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>25-12-25</p></td></tr><tr><td rowspan="1" colspan="2" data-colwidth="127,552"><p>Exporter: CMPak Limited</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Carrier:</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>Air Shipment</p></td></tr><tr><td rowspan="1" colspan="2" data-colwidth="127,552"><p>Address:\u{A0} CMPAK, CMPak Limited PLOT NO 47, NATIONAL PARK AREA, KURI ROAD, CHAK SHAHZAD, ISLAMABAD, PAKISTAN</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Term of transport:</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>CPT</p></td></tr><tr><td rowspan="1" colspan="2" data-colwidth="127,552"><p>Tel : 0315-5083340</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Port of loading :</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>ISB</p></td></tr><tr><td rowspan="1" colspan="2" data-colwidth="127,552"><p>Contact person : Muhammad Bilal</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Port of destination:</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>HK</p></td></tr><tr><td rowspan="1" colspan="2" data-colwidth="127,552"><p>Consignee Name: Huawei Tech Investment Co Ltd</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Shipment no :</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>qiw2349829</p></td></tr><tr><td rowspan="3" colspan="2" data-colwidth="127,552"><p>Add: Sinotrans S21 entrance, Lot 3221, DD129, Ping Ha Road, Lau Fau Shan, Yuen Long,N.T. Hong Kong<br>Contact person : Cynthia &amp; Chen Lei<br>Tel:\u{A0}\u{A0} (852) 2156 5902/5919<br>Fax:\u{A0} (852) 3747 1901</p></td><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Contract no :</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p></p></td></tr><tr><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}Insurance:</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p></p></td></tr><tr><td rowspan="1" colspan="1" data-colwidth="405"><p>\u{A0}No. of Pages:</p></td><td rowspan="1" colspan="1" data-colwidth="25"><p>1 of 1</p></td></tr></tbody></table><p></p><p>                                                                                                                                          <div class="table-variable-content"><table><tbody><tr><td rowspan="1" colspan="1"><p>P.O Number</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>ETA(By Supplier)</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Mode of Shipment</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Type of Shipment</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>ETA(updated)</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>Week Plan</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Supplier</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Phase</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>PO Description</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>ETA(By Contract)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Plan (Status)</p></td></tr><tr><td rowspan="1" colspan="1"><p>149174</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>10-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Telco</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>COMBA TELECOM LTD</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Phase-16</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>2025 – RAN &amp; BSS - IBS/Repeater (Comba 7 Repeater &amp; Accessories ) – Phase 16 – Supply, Payment term &amp; delivery as per contract. Taxes as per law.</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>13-Nov-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>\u{A0}Delay</p></td></tr><tr><td rowspan="1" colspan="1"><p>149492</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>10-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Air</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Telco</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Ericsson AB</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Telco Spares</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>Forecasted Telco Spare Ericsson. Duration: Y25. Payment terms and delivery as per contract. Taxes as per Law. Carcode 5OM2523</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>10-Feb-26</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>in Time</p></td></tr><tr><td rowspan="1" colspan="1"><p>149605</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>12-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>MBB Devices</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>19-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Shanghai Notion Information Technology Co., LTD</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>MBB</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>1) Forecasted PR Request of 30000 MBB Devices , BPA # 145042. 2) Payment and other terms as per the contract. 3) Tax as per law</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>24-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>in Time</p></td></tr><tr><td rowspan="1" colspan="1"><p>149334</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Sim Cards</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>EastcomPeace Technology Co. Ltd.</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>SIMs</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>Forecasted: 64K 4G SIM Card\u{A0} 2) Payment and other terms as per the contract. 3) Tax as per law</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>10-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>\u{A0}Delay</p></td></tr><tr><td rowspan="1" colspan="1"><p>149627</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Sim Cards</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>EastcomPeace Technology Co. Ltd.</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>SIMs</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>Forecasted: 64K 4G SIM Card\u{A0} 2) Payment and other terms as per the contract. 3) Tax as per law</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>25-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>in Time</p></td></tr><tr><td rowspan="1" colspan="1"><p>149756</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p>20-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Telco</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>20-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK3 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>WUHAN FIBERHOME INTERNATIONAL TECHNOLOGIES CO. LIMITED</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Phase-16</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>2025 – RAN &amp; BSS - Antenna Batch-4 (Fiberhome 300 DB Antenna) – Phase 16 – Supply, Payment term &amp; delivery as per contract. Taxes as per law.</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>17-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>\u{A0}Delay</p></td></tr><tr><td rowspan="1" colspan="1"><p>149456</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p></p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Sea</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Telco</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>30-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK4 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>ZTE CORPORATION</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Phase-16</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>ZTE Y2025 (Phase 16.1) - Metro &amp; RC Expansion Batch-3 (ZTE FSA: Phase 16.1) - Supply PR (TXN).Imp Team NC. Payment terms and other conditions including delivery timelines as per contract. Taxes as per Law.</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>23-Feb-26</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>in Time</p></td></tr><tr><td rowspan="1" colspan="1"><p>149292</p></td><td rowspan="1" colspan="1" data-colwidth="57"><p></p></td><td rowspan="1" colspan="1" data-colwidth="33"><p>Air</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>Telco</p></td><td rowspan="1" colspan="1" data-colwidth="59"><p>30-Dec-25</p></td><td rowspan="1" colspan="1" data-colwidth="74"><p>WK4 (DEC)</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>ZTE CORPORATION</p></td><td rowspan="1" colspan="1" data-colwidth="52"><p>Misc.</p></td><td rowspan="1" colspan="1" data-colwidth="115"><p>AI Ecosystem for Digital Transformation &amp; AI+ Plan (OFF-Shore). Duration as per the contract. Payment terms including delivery timelines as per contract. Taxes as per law.</p></td><td rowspan="1" colspan="1" data-colwidth="81"><p>27-Nov-25</p></td><td rowspan="1" colspan="1" data-colwidth="63"><p>in Time</p></td></tr></tbody></table></div></p>';

        $splitter = new HTMLPageSplitter();
        return $splitter->splitContent($content);
    }

    // Add this method to update config
    public function updateSplitConfig(array $config): void
    {
        $this->splitConfig = array_merge($this->splitConfig, $config);
    }

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

            // Calculate total pages from template quantities
            $totalPages = 0;
            $startSerials = [];
            $endSerials = [];

            foreach ($data['templates'] as $templateId => $templateData) {
                $quantity = $templateData['quantity'] ?? 0;
                $totalPages += $quantity;

                if (isset($templateData['start_serial'])) {
                    $startSerials[] = $templateData['start_serial'];
                }
                if (isset($templateData['end_serial'])) {
                    $endSerials[] = $templateData['end_serial'];
                }
            }

            // Get overall start and end serials
            $overallStartSerial = !empty($startSerials) ? min($startSerials) : null;
            $overallEndSerial = !empty($endSerials) ? max($endSerials) : null;

            // Create a single print job for all templates
            $printJob = PrintJob::create([
                'user_id' => auth()->id(),
                'templates' => $this->templates->pluck('id')->toArray(),
                'variable_data' => collect($data['templates'])->mapWithKeys(function ($templateData, $templateId) {
                    return [$templateId => $templateData['variable_data']];
                })->toArray(),
                'margins' => $marginsWithSettings,
                'quantity' => $totalPages, // This is total pages across all templates
                'start_serial' => $overallStartSerial,
                'end_serial' => $overallEndSerial,
                'letterhead_id' => $globalLetterheadId,
                'status' => 'completed',
            ]);

            // Allocate serials with template assignments - THIS ALREADY WORKS!
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
                ->body("Print job has been marked as completed. {$totalPages} pages allocated successfully.")
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

    private function calculateTemplatePageSerials($templateId, $pageCount): array
    {
        // Try to get from existing data first
        $startSerial = $this->startSerials[$templateId] ?? null;

        if (!$startSerial) {
            // Calculate based on template segments
            $globalLetterheadId = $this->printData['global_letterhead_id'] ?? null;

            if ($globalLetterheadId) {
                $letterhead = Letterhead::find($globalLetterheadId);
                if ($letterhead) {
                    // Get the next available serial
                    $startSerial = $letterhead->getNextAvailableSerial();

                    if (!$startSerial) {
                        // Fallback to start_serial
                        $startSerial = $letterhead->start_serial;
                    }
                }
            }
        }

        if (!$startSerial) {
            return array_fill(0, $pageCount, 'N/A');
        }

        $serials = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $serials[] = $startSerial + $i;
        }

        return $serials;
    }

    private function finalizeAllSerials(array $splitPages): void
    {
        $globalLetterheadId = $this->printData['global_letterhead_id'] ?? null;

        if (!$globalLetterheadId) {
            return;
        }

        $letterhead = Letterhead::find($globalLetterheadId);
        if (!$letterhead) {
            return;
        }

        // Get current serial
        $currentSerial = $letterhead->getNextAvailableSerial() ?? $letterhead->start_serial;

        // Update template serials based on page counts
        foreach ($splitPages as $templateId => $pageData) {
            $pageCount = $pageData['pageCount'];

            if ($pageCount > 0) {
                $this->startSerials[$templateId] = $currentSerial;
                $this->endSerials[$templateId] = $currentSerial + $pageCount - 1;
                $this->quantities[$templateId] = $pageCount;

                // Update print data
                if (isset($this->printData['templates'][$templateId])) {
                    $this->printData['templates'][$templateId]['start_serial'] = $currentSerial;
                    $this->printData['templates'][$templateId]['end_serial'] = $currentSerial + $pageCount - 1;
                    $this->printData['templates'][$templateId]['quantity'] = $pageCount;
                }

                $currentSerial += $pageCount;
            }
        }

        // Update session
        session([
            'bulk_print_data' => $this->printData,
            'bulk_print_page_counts' => $this->pageCounts,
        ]);
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

    /* New code to split content in PHP for preview and print */


    // Update getSplitPagesForPreview method to split content in PHP
    public function getSplitPagesForPreview(): array
    {
        $splitPages = [];

        // Constants for A4 page
        $a4WidthPortrait = 210; // mm
        $a4HeightPortrait = 297; // mm


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

            // Set page dimensions based on orientation
            if ($orientation === 'landscape') {
                $pageWidth = $a4HeightPortrait; // 297mm
                $pageHeight = $a4WidthPortrait; // 210mm
            } else {
                $pageWidth = $a4WidthPortrait; // 210mm
                $pageHeight = $a4HeightPortrait; // 297mm
            }


            $splitter = new HTMLPageSplitter();
            $splitContent = $splitter->splitContent($this->renderedContent[$template->id], [
                'page_width' => $pageWidth,
                'page_height' => $pageHeight,
                'margin_top' => $margins['top'],
                'margin_bottom' => $margins['bottom'],
                'margin_left' => $margins['left'],
                'margin_right' => $margins['right'],
                'font_size' =>  $fontSize
            ]);

            $actualPageCount = count($splitContent);

            // Calculate serials using the finalizeSegmentsAndSerials logic
            $pageSerials = $this->calculateTemplatePageSerials($template->id, $actualPageCount);

            $splitPages[$template->id] = [
                'template' => $template,
                'pageCount' => $actualPageCount,
                'splitContent' => $splitContent,
                'margins' => $margins,
                'orientation' => $orientation,
                'fontSize' => $fontSize,
                'pageWidth' => $pageWidth,
                'pageHeight' => $pageHeight,
                'content' => $this->renderedContent[$template->id],
                'serialRange' => $this->getTemplateSerialRange($template->id),
                'pageSerials' => $pageSerials,
                'startSerial' => $pageSerials[0] ?? null,
                'endSerial' => $pageSerials[count($pageSerials) - 1] ?? null,
            ];

            // Update session with new page count
            $this->updatePageCount($template->id, $actualPageCount);
        }

        // After all templates are processed, update serials globally
        $this->finalizeAllSerials($splitPages);

        return $splitPages;
    }


    private function updatePageCount($templateId, $newPageCount): void
    {
        // Update page counts in session
        $updated = false;
        foreach ($this->pageCounts as &$segment) {
            if (isset($segment['templateId']) && $segment['templateId'] == $templateId) {
                $segment['pageCount'] = $newPageCount;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $this->pageCounts[] = [
                'templateId' => $templateId,
                'pageCount' => $newPageCount
            ];
        }

        session(['bulk_print_page_counts' => $this->pageCounts]);
    }

    /*end of new bunch code */

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
