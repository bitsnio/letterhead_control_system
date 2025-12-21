<x-filament-panels::page>
    <div>
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

            /* Page count indicator */
            .page-count-badge {
                background: #3b82f6;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                margin-left: 10px;
            }

            .serial-badge {
                background: #10b981;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                margin-left: 10px;
            }

            .loading-indicator {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                font-size: 12px;
                color: #6b7280;
            }

            .loading-indicator .spinner {
                width: 12px;
                height: 12px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3b82f6;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            /* Page navigation controls */
            .page-nav {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
            }

            .page-nav-btn {
                padding: 4px 12px;
                background: white;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
            }

            .page-nav-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .page-nav-info {
                font-size: 12px;
                color: #475569;
                font-weight: 500;
            }

            /* Page stack effect */
            .a4-page-stack {
                position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .a4-page-stack:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }

            /* Page split indicator */
            .page-split-indicator {
                position: absolute;
                top: 10px;
                right: 10px;
                background: rgba(59, 130, 246, 0.9);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
                z-index: 10;
            }
        </style>

        <div class="space-y-6">
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 15px; border-radius: 8px;" class="no-print">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0 0 5px 0; font-weight: bold; font-size: 14px;">Print Preview Ready</h3>
                        <p style="margin: 0; font-size: 13px;">
                            @php $serialInfo = $this->getSerialInfo(); @endphp
                            Print Quantity: <strong>{{ $serialInfo['quantity'] }}</strong> | Serial(s): <strong>{{ $serialInfo['serial_display'] }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- SIMPLIFIED: All content generated in Blade/PHP -->

            <!-- $this->debugContentSplitting(); -->

            <!-- dd($this->pageSplitter()); -->
            @php

            $splitPagesData = $this->getSplitPagesForPreview();
            // dd($splitPagesData)

            @endphp

            @foreach($splitPagesData as $templateId => $pageData)
            @php
            $template = $pageData['template'];
            $splitContent = $pageData['splitContent'];
            $margins = $pageData['margins'];
            $orientation = $pageData['orientation'];
            $fontSize = $pageData['fontSize'];
            $pageCount = $pageData['pageCount'];
            $serialRange = $pageData['serialRange'];
            $pageSerials = $pageData['pageSerials'];
            @endphp

            <div class="custom-card" id="template-{{ $templateId }}"
                x-data="{ 
                    showSettings: false,
                    currentPage: 1,
                    totalPages: {{ $pageCount }},
                   
                 }">
                <!-- pageSerials: @json($pageSerials) -->
                <!-- Header -->
                <div class="custom-header">
                    <div>
                        <h3 style="margin: 0; font-weight: 600; color: #374151;">{{ $template->name }}</h3>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                            <p style="margin: 0; font-size: 12px; color: #6b7280;">
                                Template {{ $loop->iteration }} of {{ count($splitPagesData) }}
                            </p>
                            <span class="page-count-badge">{{ $pageCount }} pages</span>
                            <span class="serial-badge">{{ $serialRange }}</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <!-- Page navigation -->
                        @if($pageCount > 1)
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button @click="currentPage = Math.max(1, currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="page-nav-btn">
                                ← Prev
                            </button>
                            <span class="page-nav-info">
                                Page <span x-text="currentPage"></span> of {{ $pageCount }}
                            </span>
                            <button @click="currentPage = Math.min({{ $pageCount }}, currentPage + 1)"
                                :disabled="currentPage === {{ $pageCount }}"
                                class="page-nav-btn">
                                Next →
                            </button>
                        </div>
                        @endif

                        <button @click="showSettings = !showSettings"
                            class="page-nav-btn"
                            style="background: #f59e0b; color: white; border-color: #f59e0b;">
                            <span x-text="showSettings ? 'Hide Settings' : 'Show Settings'"></span>
                        </button>
                    </div>
                </div>

                <!-- Settings -->
                <div x-show="showSettings" x-collapse style="border-bottom: 1px solid #e5e7eb; background: #fff;">
                    <div class="custom-body">
                        <div class="custom-row">
                            <div class="custom-col">
                                <label class="control-label">Page Orientation</label>
                                <div style="display: flex; gap: 10px;">
                                    <button type="button"
                                        wire:click="$set('orientations.{{ $templateId }}', 'portrait')"
                                        class="orient-btn {{ $orientation === 'portrait' ? 'active' : '' }}">
                                        <x-filament::icon icon="heroicon-m-document" class="w-4 h-4" /> Portrait
                                    </button>
                                    <button type="button"
                                        wire:click="$set('orientations.{{ $templateId }}', 'landscape')"
                                        class="orient-btn {{ $orientation === 'landscape' ? 'active' : '' }}">
                                        <x-filament::icon icon="heroicon-m-document" class="w-4 h-4" style="transform: rotate(-90deg);" /> Landscape
                                    </button>
                                </div>
                            </div>
                            <div class="custom-col">
                                <label class="control-label">Font Size: {{ $fontSize }}%</label>
                                <div class="range-wrapper">
                                    <input type="range"
                                        wire:model.live.debounce.200ms="fontSizes.{{ $templateId }}"
                                        class="font-size-input"
                                        data-template="{{ $templateId }}"
                                        min="50" max="200" step="5"
                                        style="flex: 1; cursor: pointer;">
                                    <input type="number"
                                        wire:model.live.debounce.200ms="fontSizes.{{ $templateId }}"
                                        class="custom-input font-size-input"
                                        data-template="{{ $templateId }}"
                                        style="width: 70px;">
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 15px; border-top: 1px solid #f3f4f6; padding-top: 15px;">
                            <label class="control-label">Margins (mm)</label>
                            <div class="margin-grid">
                                @foreach(['top', 'right', 'bottom', 'left'] as $side)
                                <input type="number"
                                    wire:model.live.debounce.200ms="margins.{{ $templateId }}.{{ $side }}"
                                    class="custom-input margin-input"
                                    data-template="{{ $templateId }}"
                                    data-side="{{ $side }}">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIMPLE: Show split pages using PHP loop -->
                <div class="a4-wrapper">
                    <div style="display: flex; flex-direction: column; gap: 20px; width: 100%; align-items: center;">
                        @foreach($splitContent as $pageIndex => $pageContent)
                        <div x-show="currentPage === {{ $pageIndex + 1 }}"
                            class="a4-page-stack a4-page {{ $orientation === 'landscape' ? 'landscape' : 'portrait' }}"
                            style="margin-bottom: 20px; border: 2px solid #3b82f6;">
                            <span class="page-split-indicator">
                                Page {{ $pageIndex + 1 }} of {{ $pageCount }}
                                @if(isset($pageSerials[$pageIndex]))
                                (Serial: {{ $pageSerials[$pageIndex] }})
                                @endif
                            </span>
                            <div style="padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm; height: 100%; box-sizing: border-box; position: relative; overflow: hidden;">
                                <div class="rich-content-wrapper" style="font-size: {{ $fontSize }}%; font-family: 'Times New Roman', Times, serif; line-height: 1.3; color: black; width: 100%; height: 100%;">
                                    {!! $pageContent !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @push('scripts')
        <script>
            function collectAllPagesForPrinting() {
                const allPages = [];

                document.querySelectorAll('.custom-card').forEach(card => {
                    const templateId = card.id.replace('template-', '');

                    const fontSize = card.querySelector(
                        `.font-size-input[data-template="${templateId}"]`
                    )?.value || '100';

                    const orientation = card
                        .querySelector('.orient-btn.active')
                        ?.textContent.toLowerCase()
                        .includes('landscape') ?
                        'landscape' :
                        'portrait';

                    console.log(orientation);

                    const margins = {
                        top: '20',
                        right: '20',
                        bottom: '20',
                        left: '20'
                    };
                    card.querySelectorAll(`.margin-input[data-template="${templateId}"]`)
                        .forEach(input => {
                            margins[input.dataset.side] = input.value || '20';
                        });

                    const width = orientation === 'landscape' ? 297 : 210;
                    const height = orientation === 'landscape' ? 210 : 297;

                    card.querySelectorAll('.a4-page-stack').forEach(pageEl => {
                        const contentEl = pageEl.querySelector('.rich-content-wrapper');
                        if (!contentEl) return;

                        allPages.push({
                            content: contentEl.innerHTML,
                            orientation,
                            margins: {
                                ...margins
                            },
                            fontSize,
                            width,
                            height
                        });
                    });
                });

                return allPages;
            }

            window.prepareAndPrint = function() {
                const allPages = collectAllPagesForPrinting();
                const hasLandscape = allPages.some(p => p.orientation === 'landscape');
                const orientationCss = hasLandscape ?
                    '@page { size: A4 landscape; margin: 0; }' :
                    '@page { size: A4 portrait; margin: 0; }';

                if (!allPages.length) {
                    alert('No pages found to print.');
                    return;
                }

                const printWindow = window.open('', '_blank');
                let printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
            <style>
            ${orientationCss}
            * { 
                box-sizing: border-box; 
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            body { 
                margin: 0; 
                padding: 0;
                background: white; 
            }

            .print-page {
                position: relative;
                background: white;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .print-page:last-child {
                page-break-after: auto;
            }

            .content-area {

                line-height: 1.3;
                font-family: "Times New Roman", Times, serif;
                height: 100%;
                overflow: hidden;  
            }
            
            table {
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                width: 100% !important;
                table-layout: fixed !important;
                page-break-inside: avoid;
            }

            table td,
            table th {
                border: 1px solid #000 !important;
                padding: 0px 0px !important;
                vertical-align: top !important;
                word-break: break-word;
                overflow-wrap: break-word;
            }
            
            thead {
                display: table-header-group;
            }
            
            tr {
                page-break-inside: auto;
            }
            
            @media print {
               
                
                .print-page {
                    page-break-after: always;
                    page-break-inside: avoid;
                    margin: 0;
                }
                
                .print-page:last-child {
                    page-break-after: auto;
                }
            }
            </style>
            </head>
            <body>`;

             // html, body {
                //     width: 210mm;
                //     height: 297mm;
                // }
                allPages.forEach((page, index) => {
                    const m = page.margins;
                    console.log("pages", page);
                    printHtml += `
                <div class="print-page" style="
                    width: ${page.width}mm;
                    height: ${page.height}mm;
                    padding: ${m.top}mm ${m.right}mm ${m.bottom}mm ${m.left}mm;
                ">
                    <div class="content-area" style="font-size: ${page.fontSize}%;">
                        ${page.content}
                    </div>
                </div>`;
                });

                printHtml += '<\/body><\/html>';
                console.log(printHtml);
                printWindow.document.open();
                printWindow.document.write(printHtml);
                printWindow.document.close();

                setTimeout(() => {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.onafterprint = () => printWindow.close();
                }, 500);
            };

            window.addEventListener('print-document', () => {
                if (typeof window.prepareAndPrint === 'function') {
                    window.prepareAndPrint();
                }
            });
        </script>
        @endpush
    </div>

</x-filament-panels::page>