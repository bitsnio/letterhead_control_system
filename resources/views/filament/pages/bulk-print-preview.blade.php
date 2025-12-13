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

    <!-- Hidden div for print pages data -->
    <div id="print-pages-data" style="display: none;"
        data-pages='@json($this->getPrintPagesData())'></div>

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
                <div id="page-calculation-status" class="loading-indicator" style="display: none;">
                    <div class="spinner"></div>
                    <span>Calculating pages...</span>
                </div>
            </div>
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

                        // Get page count for this template
                        $pageCount = 1;
                        $serialRange = $this->getTemplateSerialRange($template->id);
                        foreach($pageCounts as $segment) {
                            if (isset($segment['templateId']) && $segment['templateId'] == $template->id) {
                                $pageCount = $segment['pageCount'] ?? 1;
                                break;
                            }
                    }
                    @endphp

                    <div class="custom-card" id="template-{{ $template->id }}" x-data="{ showSettings: false }" wire:key="template-{{ $template->id }}">

                        <div class="custom-header">
                            <div>
                                <h3 style="margin: 0; font-weight: 600; color: #374151;">{{ $template->name }}</h3>
                                <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                                    <p style="margin: 0; font-size: 12px; color: #6b7280;">Template {{ $index + 1 }} of {{ count($templates) }}</p>
                                    @if($pageCount > 1)
                                    <span class="page-count-badge">{{ $pageCount }} pages</span>
                                    @endif
                                    @if($serialRange !== 'Not set')
                                    <span class="serial-badge">{{ $serialRange }}</span>
                                    @endif
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
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

    @push('scripts')
    <script>
        // A4 dimensions in mm and pixels (at 96 DPI)
        const A4_WIDTH_MM = 210;
        const A4_HEIGHT_MM = 297;
        const MM_TO_PX = 3.7795275591; // 1mm = 3.78px approximately

        // Function to show/hide loading indicator
        function showLoading(show) {
            const statusEl = document.getElementById('page-calculation-status');
            if (statusEl) {
                statusEl.style.display = show ? 'flex' : 'none';
            }
        }

        // Function to calculate page segments
        function calculatePageSegments() {
            showLoading(true);

            const segmentData = [];
            let totalPages = 0;

            // Get the pages data from hidden div
            const pagesDataElement = document.getElementById('print-pages-data');
            if (!pagesDataElement) {
                console.error('Print pages data not found');
                showLoading(false);
                return {
                    segmentData,
                    totalPages
                };
            }

            const pagesData = JSON.parse(pagesDataElement.dataset.pages || '[]');

            // Create a temporary hidden container for measurement
            const tempContainer = document.createElement('div');
            tempContainer.style.cssText = `
            position: fixed;
            left: -9999px;
            top: 0;
            width: ${A4_WIDTH_MM}mm;
            visibility: hidden;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.3;
        `;
            document.body.appendChild(tempContainer);

            // Calculate pages for each template
            pagesData.forEach(template => {
                if (!template.content) return;

                // Create a measurement div for this template
                const measureDiv = document.createElement('div');
                measureDiv.style.cssText = `
                width: ${template.contentWidth}mm;
                font-size: ${template.fontSize}%;
                padding: 0;
                margin: 0;
                box-sizing: border-box;
            `;

                // Add the content
                measureDiv.innerHTML = template.content;
                tempContainer.appendChild(measureDiv);

                // Get the actual height
                const contentHeightPx = measureDiv.scrollHeight;
                const maxHeightPx = template.contentHeight * MM_TO_PX;

                // Calculate number of pages needed (round up)
                const pageCount = Math.max(1, Math.ceil(contentHeightPx / maxHeightPx));

                segmentData.push({
                    templateId: template.templateId,
                    templateName: template.templateName,
                    pageCount: pageCount,
                    contentHeightPx: contentHeightPx,
                    maxHeightPx: maxHeightPx,
                    fontSize: template.fontSize,
                    contentWidth: template.contentWidth,
                    content: template.content
                });

                totalPages += pageCount;

                // Clean up
                tempContainer.removeChild(measureDiv);
            });

            // Remove temporary container
            document.body.removeChild(tempContainer);

            // Send data back to Livewire
            @this.call('finalizeSegmentsAndSerials', segmentData);

            showLoading(false);
            return {
                segmentData,
                totalPages
            };
        }

        // Improved function to split content into pages
        function splitContentIntoPages(content, maxHeightPx, fontSize, contentWidth) {
            const pages = [];

            // Create a temporary div for measurement
            const tempDiv = document.createElement('div');
            tempDiv.style.cssText = `
            position: absolute;
            left: -9999px;
            top: 0;
            width: ${contentWidth}mm;
            font-size: ${fontSize}%;
            visibility: hidden;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.3;
            overflow: hidden;
        `;
            document.body.appendChild(tempDiv);

            let remainingContent = content;
            let pageNumber = 1;

            // Function to check if content fits
            function doesContentFit(testContent) {
                tempDiv.innerHTML = testContent;
                return tempDiv.scrollHeight <= maxHeightPx;
            }

            // Split content until we have nothing left
            while (remainingContent.trim().length > 0) {
                // If all remaining content fits on one page
                if (doesContentFit(remainingContent)) {
                    pages.push(remainingContent);
                    break;
                }

                // We need to split the content
                // Start with the entire content and work backwards
                let low = 0;
                let high = remainingContent.length;
                let bestFitIndex = 0;
                let bestFitContent = '';

                // Binary search for the best split point
                while (low <= high) {
                    const mid = Math.floor((low + high) / 2);
                    const testContent = remainingContent.substring(0, mid);

                    tempDiv.innerHTML = testContent;
                    const fits = tempDiv.scrollHeight <= maxHeightPx;

                    if (fits) {
                        // This fits, try to include more
                        bestFitIndex = mid;
                        bestFitContent = testContent;
                        low = mid + 1;
                    } else {
                        // Doesn't fit, try less content
                        high = mid - 1;
                    }
                }

                // Try to find a better break point (at the end of a tag or sentence)
                if (bestFitIndex > 0) {
                    // Look for the last closing tag
                    const tagMatches = [
                        /<\/div>/gi,
                        /<\/p>/gi,
                        /<\/h[1-6]>/gi,
                        /<\/li>/gi,
                        /<\/tr>/gi,
                        /<\/td>/gi,
                        /<\/th>/gi,
                        /<\/table>/gi,
                        /<br\s*\/?>/gi,
                        /<hr\s*\/?>/gi
                    ];

                    let bestBreakPoint = bestFitIndex;

                    // Search for the last occurrence of any tag end
                    for (const regex of tagMatches) {
                        regex.lastIndex = 0;
                        let match;
                        while ((match = regex.exec(remainingContent.substring(0, bestFitIndex))) !== null) {
                            const matchEnd = match.index + match[0].length;
                            if (matchEnd <= bestFitIndex) {
                                // Test if content up to this point fits
                                const testContent = remainingContent.substring(0, matchEnd);
                                tempDiv.innerHTML = testContent;
                                if (tempDiv.scrollHeight <= maxHeightPx) {
                                    bestBreakPoint = matchEnd;
                                    bestFitContent = testContent;
                                }
                            }
                        }
                    }

                    // If we found a better break point, update
                    if (bestBreakPoint > 0 && bestBreakPoint !== bestFitIndex) {
                        bestFitIndex = bestBreakPoint;
                    }
                }

                // If we couldn't find any fitting content, force a split
                if (bestFitIndex === 0) {
                    // Emergency split - just take half
                    bestFitIndex = Math.floor(remainingContent.length / 2);
                    bestFitContent = remainingContent.substring(0, bestFitIndex);

                    // Try to find a space to break at
                    const lastSpace = bestFitContent.lastIndexOf(' ');
                    if (lastSpace > 0) {
                        bestFitIndex = lastSpace + 1;
                        bestFitContent = remainingContent.substring(0, bestFitIndex);
                    }
                }

                // Add the page
                pages.push(bestFitContent);

                // Update remaining content
                remainingContent = remainingContent.substring(bestFitIndex).trim();

                // Add "continued" marker to the beginning of remaining content for next page
                if (remainingContent.length > 0) {
                    remainingContent = '<div style="color: #666; font-style: italic; font-size: 10pt; padding: 5px 0; border-bottom: 1px dashed #ccc; margin-bottom: 10px;">(Continued from previous page...)</div>' + remainingContent;
                }

                pageNumber++;
            }

            // Clean up
            document.body.removeChild(tempDiv);

            return pages;
        }

        // Function to generate print HTML
        function generatePrintHtml(pagesData) {
            let html = '<!DOCTYPE html><html><head>';
            html += '<meta charset="utf-8">';
            html += '<meta name="viewport" content="width=device-width, initial-scale=1">';
            html += '<title>Print Document</title>';
            html += '<style>';

            // Critical print styles
            html += `
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
            
            img {
                max-width: 100% !important;
                height: auto !important;
                page-break-inside: avoid;
            }
            
            h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
                margin: 10px 0 5px 0;
            }
            
            p {
                margin: 5px 0;
                line-height: 1.3;
            }
            
            * {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                hyphens: auto;
            }
            
            /* For multi-page content */
            .continued-header {
                color: #666;
                font-style: italic;
                font-size: 10pt;
                padding: 5px 0;
                border-bottom: 1px dashed #ccc;
                margin-bottom: 10px;
            }
        `;

            html += '<\/style><\/head><body>';

            // Generate pages for each template
            pagesData.forEach((template, templateIndex) => {
                const pageCount = template.pageCount || 1;

                // Split content into pages
                const maxHeightPx = template.contentHeight * MM_TO_PX;
                const contentPages = splitContentIntoPages(
                    template.content,
                    maxHeightPx,
                    template.fontSize,
                    template.contentWidth
                );

                console.log(`Template "${template.templateName}": ${pageCount} pages needed, split into ${contentPages.length} pages`);

                // Generate pages for this template
                contentPages.forEach((pageContent, pageIndex) => {
                    const isLast = (templateIndex === pagesData.length - 1 &&
                        pageIndex === contentPages.length - 1);
                    const orientationClass = template.orientation === 'landscape' ? 'landscape' : 'portrait';
                    const lastClass = isLast ? 'last-page' : '';

                    html += `
                    <div class="print-page ${orientationClass} ${lastClass}"
                        style="width: ${template.pageWidth}mm; height: ${template.pageHeight}mm; position: relative;">
                        <div class="print-doc"
                            style="
                                margin: ${template.margins.top}mm ${template.margins.right}mm ${template.margins.bottom}mm ${template.margins.left}mm; 
                                font-size: ${template.fontSize}%; 
                                font-family: 'Times New Roman', Times, serif;
                                width: ${template.contentWidth}mm;
                                height: ${template.contentHeight}mm;
                                overflow: hidden;
                                line-height: 1.3;
                            ">
                            <div style="width: 100%; height: 100%; overflow: hidden; word-wrap: break-word;">
                `;

                    html += pageContent;

                    html += `
                            </div>
                        </div>
                    </div>
                `;
                });
            });

            html += '<\/body><\/html>';
            return html;
        }

        // Main print function
        window.prepareAndPrint = function() {
            showLoading(true);

            // First calculate page counts
            const result = calculatePageSegments();

            // Wait a bit for Livewire to update
            setTimeout(() => {
                // Get updated pages data
                const pagesDataElement = document.getElementById('print-pages-data');
                if (!pagesDataElement) {
                    console.error('Print pages data not found');
                    showLoading(false);
                    return;
                }

                let pagesData = JSON.parse(pagesDataElement.dataset.pages || '[]');

                // Update page counts in pagesData based on calculation
                pagesData = pagesData.map(template => {
                    const segment = result.segmentData.find(s => s.templateId === template.templateId);
                    if (segment) {
                        return {
                            ...template,
                            pageCount: segment.pageCount,
                            // Update with the content from segment (in case it was modified)
                            content: segment.content || template.content
                        };
                    }
                    return template;
                });

                console.log('Pages data for printing:', pagesData);

                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    alert('Please allow popups for this website');
                    showLoading(false);
                    return;
                }

                // Generate print HTML
                const printHtml = generatePrintHtml(pagesData);

                printWindow.document.open();
                printWindow.document.write(printHtml);
                printWindow.document.close();

                printWindow.onload = function() {
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();

                        printWindow.onafterprint = function() {
                            setTimeout(() => {
                                printWindow.close();
                                showLoading(false);
                            }, 300);
                        };

                        // Fallback in case onafterprint doesn't fire
                        setTimeout(() => {
                            if (!printWindow.closed) {
                                printWindow.close();
                                showLoading(false);
                            }
                        }, 5000);
                    }, 500);
                };

                // Handle case where window fails to load
                setTimeout(() => {
                    if (printWindow && printWindow.document.readyState === 'complete') {
                        // Window loaded successfully
                    } else {
                        showLoading(false);
                    }
                }, 3000);

            }, 2000); // Give Livewire time to update
        };

        // Calculate pages when preview loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                calculatePageSegments();
            }, 1500);
        });

        // Listen for content updates from Livewire
        window.addEventListener('content-updated', () => {
            // Small delay to ensure Livewire updates are complete
            setTimeout(() => {
                calculatePageSegments();
            }, 800);
        });

        // Listen for print event
        window.addEventListener('print-document', () => {
            console.log('Print document event received');
            if (typeof window.prepareAndPrint === 'function') {
                window.prepareAndPrint();
            } else {
                console.error('prepareAndPrint function not defined');
            }
        });

        // Debug helper
        window.debugContentSplitting = function() {
            const pagesDataElement = document.getElementById('print-pages-data');
            const pagesData = JSON.parse(pagesDataElement.dataset.pages || '[]');

            pagesData.forEach(template => {
                console.log(`--- Template: ${template.templateName} ---`);
                console.log(`Content length: ${template.content.length} characters`);
                console.log(`Page count: ${template.pageCount}`);
                console.log(`Content preview: ${template.content.substring(0, 200)}...`);
            });
        };
    </script>

    @endpush
</x-filament-panels::page>