<x-filament-panels::page>
    <div class="no-print" style="margin-bottom: 20px;">
        @if ($printJobId)
        <x-filament::button
            icon="heroicon-m-arrow-left"
            color="gray"
            size="lg"
            tag="a"
            href="{{ route('filament.admin.resources.letterhead-templates.index') }}"
            style="margin-left: 10px;">
            Return to Templates
        </x-filament::button>
        @endif
    </div>

    <style>
        /* UI Styles */
        .custom-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .custom-header {
            background: #f9fafb;
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-body {
            padding: 15px;
        }

        .custom-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .custom-col {
            flex: 1;
            min-width: 200px;
        }

        .control-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .range-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .margin-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .custom-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 14px;
        }

        .orient-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .orient-btn.active {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        /* A4 Page Simulation for Preview */
        .a4-wrapper {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            overflow: auto;
        }

        .a4-page {
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .a4-page.portrait {
            width: 210mm;
            min-height: 297mm;
        }

        .a4-page.landscape {
            width: 297mm;
            min-height: 210mm;
        }

        @media (max-width: 1000px) {
            .a4-page {
                transform: scale(0.8);
                transform-origin: top center;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            body * {
                visibility: hidden;
            }
            
            #print-content,
            #print-content * {
                visibility: visible;
            }
            
            #print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }

        /* FIX: Rich Editor Table Borders for PREVIEW */
        .rich-content-wrapper {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .rich-content-wrapper table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin: 0.5em 0 !important;
            page-break-inside: avoid;
            table-layout: fixed;
        }

        .rich-content-wrapper table td,
        .rich-content-wrapper table th {
            border: 1px solid #000 !important;
            padding: 4px 6px !important;
            font-size: 11pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
        }
        
        .rich-content-wrapper table th {
            background-color: #f5f5f5 !important;
            font-weight: bold;
        }
        
        /* Ensure content doesn't overflow */
        .rich-content-wrapper * {
            max-width: 100% !important;
            box-sizing: border-box;
        }
        
        .rich-content-wrapper img {
            max-width: 100% !important;
            height: auto !important;
        }
    </style>

    <div class="space-y-6">
        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 8px;" class="no-print">
            <h3 style="margin: 0 0 5px 0; font-weight: bold; font-size: 14px;">Print Preview Ready</h3>
            <p style="margin: 0; font-size: 13px;">
                @php $serialInfo = $this->getSerialInfo(); @endphp
                Print Quantity: <strong>{{ $serialInfo['quantity'] }}</strong> | Serial(s): <strong>{{ $serialInfo['serial_display'] }}</strong>
            </p>
        </div>

        <div class="no-print">
            @foreach($templates as $index => $template)
            @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
            @php
            $margins = $this->getTemplateMargins($template->id);
            $orientation = $this->getTemplateOrientation($template->id);
            $fontSize = $this->getTemplateFontSize($template->id);
            $contentWidth = $orientation === 'landscape' ? '297mm' : '210mm';
            $contentHeight = $orientation === 'landscape' ? '210mm' : '297mm';
            @endphp

            <div class="custom-card" id="template-{{ $template->id }}" x-data="{ showSettings: false }" wire:key="template-{{ $template->id }}">

                <div class="custom-header">
                    <div>
                        <h3 style="margin: 0; font-weight: 600; color: #374151;">{{ $template->name }}</h3>
                        <p style="margin: 0; font-size: 12px; color: #6b7280;">Template {{ $index + 1 }} of {{ count($templates) }}</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="font-size: 12px; color: #6b7280;">Serials: {{ $this->getTemplateSerialRange($template->id) }}</span>
                        <x-filament::button size="sm" color="warning" icon="heroicon-m-cog-6-tooth" x-on:click="showSettings = !showSettings" type="button">
                            <span x-text="showSettings ? 'Hide Settings' : 'Show Settings'"></span>
                        </x-filament::button>
                    </div>
                </div>

                <div x-show="showSettings" x-collapse style="border-bottom: 1px solid #e5e7eb; background: #fff;">
                    <div class="custom-body">
                        <div class="custom-row">
                            <div class="custom-col">
                                <label class="control-label">Page Orientation</label>
                                <div style="display: flex; gap: 10px;">
                                    <button type="button" wire:click="$set('orientations.{{ $template->id }}', 'portrait')" class="orient-btn {{ $orientation === 'portrait' ? 'active' : '' }}">
                                        <x-filament::icon icon="heroicon-m-document" class="w-4 h-4" /> Portrait
                                    </button>
                                    <button type="button" wire:click="$set('orientations.{{ $template->id }}', 'landscape')" class="orient-btn {{ $orientation === 'landscape' ? 'active' : '' }}">
                                        <x-filament::icon icon="heroicon-m-document" class="w-4 h-4" style="transform: rotate(-90deg);" /> Landscape
                                    </button>
                                </div>
                            </div>
                            <div class="custom-col">
                                <label class="control-label">Font Size: {{ $fontSize }}%</label>
                                <div class="range-wrapper">
                                    <input type="range" wire:model.live.debounce.200ms="fontSizes.{{ $template->id }}" min="50" max="200" step="5" style="flex: 1; cursor: pointer;">
                                    <input type="number" wire:model.live.debounce.200ms="fontSizes.{{ $template->id }}" class="custom-input" style="width: 70px;">
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 15px; border-top: 1px solid #f3f4f6; padding-top: 15px;">
                            <label class="control-label">Margins (mm)</label>
                            <div class="margin-grid">
                                @foreach(['top', 'right', 'bottom', 'left'] as $side)
                                <div>
                                    <span style="font-size: 10px; color: #9ca3af; text-transform: uppercase;">{{ $side }}</span>
                                    <input type="number" wire:model.live.debounce.200ms="margins.{{ $template->id }}.{{ $side }}" min="0" max="50" class="custom-input">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="a4-wrapper">
                    <div class="a4-page {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }}">
                        <div style="padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm; height: 100%; box-sizing: border-box; position: relative; overflow: hidden;">
                            <div class="rich-content-wrapper" style="font-size: {{ $fontSize }}%; font-family: 'Times New Roman', Times, serif; line-height: 1.3; color: black; width: 100%; height: 100%;">
                                {!! $renderedContent[$template->id] !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    <div id="print-content" style="display: none;">
        @foreach($templates as $index => $template)
        @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
        @php
        $margins = $this->getTemplateMargins($template->id);
        $orientation = $this->getTemplateOrientation($template->id);
        $fontSize = $this->getTemplateFontSize($template->id);
        $isLast = $index === count($templates) - 1;
        
        // Calculate actual content width and height after margins
        $pageWidth = $orientation === 'landscape' ? 297 : 210;
        $pageHeight = $orientation === 'landscape' ? 210 : 297;
        $contentWidth = $pageWidth - $margins['left'] - $margins['right'];
        $contentHeight = $pageHeight - $margins['top'] - $margins['bottom'];
        @endphp
        <div class="print-page {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }} {{ $isLast ? 'last-page' : '' }}" 
             data-orientation="{{ $orientation }}" 
             data-index="{{ $index }}"
             style="width: {{ $pageWidth }}mm; height: {{ $pageHeight }}mm; position: relative;">
            <div class="print-doc" 
                 style="
                    margin: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm; 
                    font-size: {{ $fontSize }}%; 
                    font-family: 'Times New Roman', Times, serif;
                    width: {{ $contentWidth }}mm;
                    height: {{ $contentHeight }}mm;
                    overflow: hidden;
                    line-height: 1.3;
                 ">
                <div style="width: 100%; height: 100%; overflow: hidden; word-wrap: break-word;">
                    {!! $renderedContent[$template->id] !!}
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    @push('scripts')
    <script>
        window.prepareAndPrint = function() {
            const content = document.getElementById('print-content');
            if (!content) {
                console.error('Print content not found');
                return;
            }

            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                alert('Please allow popups for this website');
                return;
            }
            
            const doc = printWindow.document;

            doc.open();
            doc.write('<!DOCTYPE html>');
            doc.write('<html><head>');
            doc.write('<meta charset="utf-8">');
            doc.write('<meta name="viewport" content="width=device-width, initial-scale=1">');
            doc.write('<title>Print Document</title>');
            doc.write('<style>');
            
            // Critical print styles - FIXED for overflow
            doc.write(`
                @page { 
                    margin: 0;
                    size: A4;
                }
                
                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                
                html, body {
                    width: 100%;
                    height: 100%;
                    margin: 0;
                    padding: 0;
                    font-family: "Times New Roman", Times, serif;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                
                .print-page { 
                    position: relative;
                    page-break-after: always;
                    overflow: hidden !important;
                    background: white;
                }
                
                .print-page.portrait { 
                    width: 210mm; 
                    height: 297mm;
                }
                
                .print-page.landscape { 
                    width: 297mm; 
                    height: 210mm;
                }
                
                /* Remove page break from last page */
                .print-page.last-page {
                    page-break-after: avoid !important;
                }
                
                .print-doc {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    overflow: hidden;
                }
                
                .print-doc * {
                    max-width: 100% !important;
                    box-sizing: border-box;
                }
                
                /* Critical: Force table to stay within bounds */
                table {
                    border-collapse: collapse !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    table-layout: fixed !important;
                    margin: 5px 0 !important;
                    page-break-inside: avoid;
                    word-wrap: break-word;
                }
                
                table th,
                table td {
                    border: 1px solid #000 !important;
                    padding: 3px 5px !important;
                    font-size: 10pt !important;
                    word-break: break-word;
                    overflow-wrap: break-word;
                    vertical-align: top;
                    min-width: 20px;
                }
                
                table th {
                    background-color: #f0f0f0 !important;
                    font-weight: bold !important;
                }
                
                /* Prevent images from overflowing */
                img {
                    max-width: 100% !important;
                    height: auto !important;
                    page-break-inside: avoid;
                }
                
                /* Text elements */
                h1, h2, h3, h4, h5, h6 {
                    page-break-after: avoid;
                    margin: 10px 0 5px 0;
                }
                
                p {
                    margin: 5px 0;
                    line-height: 1.3;
                }
                
                ul, ol {
                    margin: 5px 0 5px 20px;
                    page-break-inside: avoid;
                }
                
                li {
                    margin: 2px 0;
                }
                
                /* Text formatting */
                strong, b {
                    font-weight: bold;
                }
                
                em, i {
                    font-style: italic;
                }
                
                u {
                    text-decoration: underline;
                }
                
                /* Force text wrapping */
                * {
                    word-wrap: break-word !important;
                    overflow-wrap: break-word !important;
                    hyphens: auto;
                }
                
                /* Handle long words and URLs */
                .print-doc {
                    overflow-wrap: break-word;
                    word-wrap: break-word;
                    word-break: break-word;
                }
            `);
            
            doc.write('<\/style>');
            doc.write('<\/head><body style="margin: 0; padding: 0;">');
            
            // Clone and append all pages
            const pages = content.querySelectorAll('.print-page');
            pages.forEach((page, index) => {
                const clonedPage = page.cloneNode(true);
                doc.write(clonedPage.outerHTML);
            });
            
            doc.write('<\/body><\/html>');
            doc.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.focus();
                    printWindow.print();
                    
                    // Close after printing (or if user cancels)
                    printWindow.onafterprint = function() {
                        setTimeout(() => {
                            printWindow.close();
                        }, 300);
                    };
                }, 300);
            };
        };

        // Listen for print event
        window.addEventListener('print-document', () => {
            if (typeof window.prepareAndPrint === 'function') {
                window.prepareAndPrint();
            } else {
                console.error('prepareAndPrint function not defined');
            }
        });
    </script>
    @endpush
</x-filament-panels::page>