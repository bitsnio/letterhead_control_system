<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Success Alert -->
        <div class="rounded-lg bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800 print:hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-success-800 dark:text-success-200">
                        Print Preview Ready
                    </h3>
                    <div class="mt-2 text-sm text-success-700 dark:text-success-300">
                        <p>
                            The preview below shows exactly how your documents will appear when printed.
                            Adjust margins and save them for future use.
                        </p>
                        @php
                        $serialInfo = $this->getSerialInfo();
                        @endphp
                        <p class="mt-2 font-semibold">
                            Print Quantity: {{ $serialInfo['quantity'] }} |
                            Serial(s): {{ $serialInfo['serial_display'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Margin Controls Form -->
        <div class="print:hidden">
            {{ $this->form }}
        </div>

        <!-- Print Simulation Preview -->
        <div class="preview-container print:hidden">
            @foreach($templates as $index => $template)
            @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
            @php
                $margins = $this->getTemplateMargins($template->id);
            @endphp
            <div class="a4-page">
                <!-- Page Header (only in preview) -->
                <div class="page-header">
                    <div class="flex items-center justify-between p-4 bg-gray-100 border-b">
                        <div>
                            <h3 class="font-semibold text-gray-700">{{ $template->name }}</h3>
                            <p class="text-sm text-gray-500">Template {{ $index + 1 }} of {{ count($templates) }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Margins: Top {{ $margins['top'] }}mm, Right {{ $margins['right'] }}mm, 
                                Bottom {{ $margins['bottom'] }}mm, Left {{ $margins['left'] }}mm
                            </p>
                        </div>
                        <div class="text-sm text-gray-500">
                            Serials: {{ $this->getTemplateSerialRange($template->id) }}
                        </div>
                    </div>
                </div>

                <!-- Actual Content Area with dynamic margins -->
                <div class="page-content" style="padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm;">
                    <div class="print-content">
                        {!! $renderedContent[$template->id] !!}
                    </div>
                </div>

                <!-- Page indicator (only in preview) -->
                <div class="page-footer text-center py-2 text-xs text-gray-400 border-t">
                    Page {{ $index + 1 }} - A4 Size Preview
                </div>
            </div>

            @if($index < count($templates) - 1)
                <div class="page-gap"></div>
            @endif
            @endif
            @endforeach
        </div>

        <!-- Hidden Print Content - Only for printing -->
        <div id="print-content" class="hidden">
            @foreach($templates as $index => $template)
            @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
            @php
                $margins = $this->getTemplateMargins($template->id);
            @endphp
            <div class="print-page">
                <div class="print-document" style="margin: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm;">
                    {!! $renderedContent[$template->id] !!}
                </div>
            </div>

            
            @endif
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        window.prepareAndPrint = function() {
            const rawContent = document.getElementById('print-content').innerHTML;

            const printWindow = window.open('', '_blank');
            const doc = printWindow.document;

            doc.open();
            doc.write('<!DOCTYPE html>');
            doc.write('<html><head>');
            doc.write('<meta charset="utf-8"><title>Print</title>');
            doc.write(`<style>
        @page { 
            size: A4; 
            margin: 0;
        }
        body { 
            font-family: "Times New Roman"; 
            font-size: 12pt; 
            margin: 0; 
            padding: 0;
        }
        .print-page { 
            page-break-after: always; 
            height: 297mm;
            width: 210mm;
        }
        .print-page:last-child { 
            page-break-after: auto; 
        }
        .print-document {
            width: 100%;
            height: 100%;
        }
    </style>`);
            doc.write('<\/head><body>');
            doc.write(rawContent);
            doc.write('<\/body><\/html>');
            doc.close();

            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 200);
            };
        };

        document.addEventListener('livewire:load', function() {
            if (window.Livewire) {
                Livewire.on('print-document', () => {
                    if (typeof window.prepareAndPrint === 'function') {
                        window.prepareAndPrint();
                    }
                });
            }
        });

        // Live margin updates
        document.addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('marginData')) {
                // Trigger Livewire update after a short delay
                setTimeout(() => {
                    if (window.Livewire) {
                        window.Livewire.find('{{ $this->getId() }}').call('updateMargins');
                    }
                }, 300);
            }
        });
    </script>
    @endpush

    <style>
        /* A4 Page Simulation for Preview */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin: 20px auto;
            position: relative;
        }

        .page-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px;
        }

        .page-content {
            min-height: calc(297mm - 30mm);
            background: white;
        }

        .page-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 8px;
        }

        .page-gap {
            height: 40px;
            background: linear-gradient(45deg, transparent 25%, rgba(0, 0, 0, 0.02) 25%,
                    rgba(0, 0, 0, 0.02) 50%, transparent 50%, transparent 75%,
                    rgba(0, 0, 0, 0.02) 75%);
            background-size: 20px 20px;
            margin: 20px auto;
            width: 210mm;
            border-radius: 4px;
        }

        /* Screen preview styling */
        .preview-container {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }

        .print-content {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
        }

        .print-content h1 {
            font-size: 16pt;
            margin-bottom: 12pt;
            font-weight: bold;
        }

        .print-content h2 {
            font-size: 14pt;
            margin-bottom: 10pt;
            font-weight: bold;
        }

        .print-content h3 {
            font-size: 13pt;
            margin-bottom: 8pt;
            font-weight: bold;
        }

        .print-content h4 {
            font-size: 12pt;
            margin-bottom: 6pt;
            font-weight: bold;
        }

        .print-content p {
            margin-bottom: 6pt;
            line-height: 1.3;
        }

        .print-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 8pt 0;
            page-break-inside: avoid;
        }

        .print-content th,
        .print-content td {
            border: 1px solid #000;
            padding: 4pt 6pt;
            font-size: 10pt;
        }

        .print-content th {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .print-content img {
            max-width: 100%;
            height: auto;
            page-break-inside: avoid;
        }

        .print-content ul,
        .print-content ol {
            margin-bottom: 8pt;
            margin-left: 20pt;
        }

        .print-content li {
            margin-bottom: 4pt;
        }

        /* Hide print content on screen */
        #print-content {
            display: none;
        }

        /* Responsive design for different screens */
        @media (max-width: 230mm) {
            .a4-page {
                transform: scale(0.9);
                transform-origin: top center;
            }

            .page-gap {
                transform: scale(0.9);
                transform-origin: top center;
            }
        }

        @media (max-width: 190mm) {
            .a4-page {
                transform: scale(0.8);
            }

            .page-gap {
                transform: scale(0.8);
            }
        }

        @media (max-width: 170mm) {
            .a4-page {
                transform: scale(0.7);
            }

            .page-gap {
                transform: scale(0.7);
            }
        }

        /* Simple print styles for direct printing (fallback) */
        @media print {

            /* Hide everything except what we want to print */
            body * {
                visibility: hidden;
            }

            #print-content,
            #print-content * {
                visibility: visible;
            }

            #print-content {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            .print-page {
                page-break-after: always;
            }

            .print-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</x-filament-panels::page>