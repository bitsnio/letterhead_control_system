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
                        </p>
                        @php
                        $serialInfo = $this->getSerialInfo();
                        @endphp
                        <p class="mt-2 font-semibold">
                            Print Quantity: {{ $serialInfo['quantity'] }} |
                            Serial(s): {{ $serialInfo['serial_display'] }}
                        </p>

                        <!-- Margin controls and template selector -->
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                            <!-- Template selector so user chooses which template's margins they edit -->
                            <div>
                                <label class="text-xs font-medium text-gray-600">Template</label>
                                <select wire:model="selectedTemplateId" class="block w-full rounded border px-2 py-1">
                                    @foreach($templates as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Margin inputs - bound to margins[selectedTemplateId] via entangle -->
                            <div x-data="{
                top: @entangle('margins.' . ($selectedTemplateId ?? 'temp') . '.top'),
                bottom: @entangle('margins.' . ($selectedTemplateId ?? 'temp') . '.bottom'),
                left: @entangle('margins.' . ($selectedTemplateId ?? 'temp') . '.left'),
                right: @entangle('margins.' . ($selectedTemplateId ?? 'temp') . '.right')
            }"
                                x-cloak
                                class="col-span-1 md:col-span-2 grid grid-cols-4 gap-2">
                                <div>
                                    <label class="text-xs">Top (mm)</label>
                                    <input type="number" x-model.number="top" wire:model.defer="margins.{{ $selectedTemplateId }}.top" class="w-full rounded border px-2 py-1">
                                </div>
                                <div>
                                    <label class="text-xs">Bottom (mm)</label>
                                    <input type="number" x-model.number="bottom" wire:model.defer="margins.{{ $selectedTemplateId }}.bottom" class="w-full rounded border px-2 py-1">
                                </div>
                                <div>
                                    <label class="text-xs">Left (mm)</label>
                                    <input type="number" x-model.number="left" wire:model.defer="margins.{{ $selectedTemplateId }}.left" class="w-full rounded border px-2 py-1">
                                </div>
                                <div>
                                    <label class="text-xs">Right (mm)</label>
                                    <input type="number" x-model.number="right" wire:model.defer="margins.{{ $selectedTemplateId }}.right" class="w-full rounded border px-2 py-1">
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <x-filament::button wire:click="saveMargins({{ $selectedTemplateId }})" class="btn-primary">
                                    Save Margins
                                </x-filament::button>

                                <x-filament::button type="button" onclick="prepareAndPrint()" class="btn-outline">
                                    Print All
                                </x-filament::button>
                            </div>
                        </div>
                    </div>

                </div>
                <x-filament::button icon="heroicon-o-printer" onclick="prepareAndPrint()" class="print:hidden">
                    Print All
                </x-filament::button>
            </div>
        </div>

        <!-- Print Simulation Preview -->
        <div class="preview-container print:hidden">
            @foreach($templates as $index => $template)
            @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
            @php
            $margin = $margins[$template->id] ?? ['top'=>15,'bottom'=>15,'left'=>15,'right'=>15];
            @endphp

            <div class="a4-page" style="padding-top: {{ $margin['top'] }}mm; padding-bottom: {{ $margin['bottom'] }}mm; padding-left: {{ $margin['left'] }}mm; padding-right: {{ $margin['right'] }}mm;">
                <!-- header, content, footer same as before -->
                <div class="page-header"> ... </div>

                <div class="page-content">
                    <div class="print-content">
                        {!! $renderedContent[$template->id] !!}
                    </div>
                </div>

                <div class="page-footer">Page {{ $index + 1 }}</div>
            </div>

            @if($index < count($templates) - 1)
                <div class="page-gap">
        </div>
        @endif
        @endif
        @endforeach
    </div>


    <!-- Hidden Print Content - Only for printing -->
    <div id="print-content" class="hidden">
        @foreach($templates as $index => $template)
            @if(isset($renderedContent[$template->id]) && !empty($renderedContent[$template->id]))
                @php
                    $margin = $margins[$template->id] ?? ['top'=>15,'bottom'=>15,'left'=>15,'right'=>15];
                @endphp

                <div class="print-page">
                    <div class="print-document" style="padding-top: {{ $margin['top'] }}mm; padding-bottom: {{ $margin['bottom'] }}mm; padding-left: {{ $margin['left'] }}mm; padding-right: {{ $margin['right'] }}mm;">
                        {!! $renderedContent[$template->id] !!}
                    </div>
                </div>
            @endif
        @endforeach
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
        @page { size: A4; margin: 15mm; }
        body { font-family: "Times New Roman"; font-size: 12pt; margin: 0; }
        .print-page { page-break-after: always; }
        .print-page:last-child { page-break-after: auto; }
    </style>`);
            doc.write('<\/head><\/body>');
            doc.write(rawContent);
            doc.write('<\/body><\/html>');
            doc.close();

            // Use onload on the popup window object (no script tags)
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 200);
            };
        };

        // Also wire Livewire event to the same global function (if you trigger via Livewire)
        document.addEventListener('livewire:load', function() {
            if (window.Livewire) {
                Livewire.on('print-document', () => {
                    if (typeof window.prepareAndPrint === 'function') {
                        window.prepareAndPrint();
                    }
                });
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
            padding: 15mm;
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