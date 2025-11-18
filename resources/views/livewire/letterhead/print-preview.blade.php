<div>

    <!-- SUCCESS / INFO BOX -->
    <div class="rounded-lg bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800 print:hidden">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-success-800 dark:text-success-200">
                    Print Preview Ready
                </h3>

                <div class="mt-2 text-sm text-success-700 dark:text-success-300">
                    <p>The preview below shows exactly how your documents will appear when printed.</p>

                    <!-- ✔ Margin Inputs -->
                    <div class="mt-4 grid grid-cols-4 gap-4">
                        <div>
                            <label class="text-xs font-semibold">Top Margin (mm)</label>
                            <input type="number" wire:model.debounce.300ms="margins.{{ $selectedTemplateId }}.top" class="w-full border p-1 rounded">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Bottom Margin (mm)</label>
                            <input type="number" wire:model.debounce.300ms="margins.{{ $selectedTemplateId }}.bottom" class="w-full border p-1 rounded">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Left Margin (mm)</label>
                            <input type="number" wire:model.debounce.300ms="margins.{{ $selectedTemplateId }}.left" class="w-full border p-1 rounded">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Right Margin (mm)</label>
                            <input type="number" wire:model.debounce.300ms="margins.{{ $selectedTemplateId }}.right" class="w-full border p-1 rounded">
                        </div>
                    </div>

                    <!-- SAVE BUTTON -->
                    <button 
                        class="mt-3 px-3 py-1 bg-success-600 text-white rounded text-sm"
                        wire:click="saveMargins({{ $selectedTemplateId }})"
                    >
                        Save Margins
                    </button>

                </div>
            </div>

            <x-filament::button icon="heroicon-o-printer" onclick="prepareAndPrint()" class="print:hidden">
                Print All
            </x-filament::button>
        </div>
    </div>



    <!-- PAGE PREVIEW -->
    <div class="preview-container print:hidden">
        @foreach ($templates as $index => $template)
            @if(isset($renderedContent[$template->id]))

                <!-- PAGE WRAPPER -->
                <div class="a4-page" 
                    style="
                        padding-top: {{ $margins[$template->id]['top'] }}mm;
                        padding-bottom: {{ $margins[$template->id]['bottom'] }}mm;
                        padding-left: {{ $margins[$template->id]['left'] }}mm;
                        padding-right: {{ $margins[$template->id]['right'] }}mm;
                    "
                    wire:click="$set('selectedTemplateId', {{ $template->id }})"
                >

                    <div class="print-content">
                        {!! $renderedContent[$template->id] !!}
                    </div>

                </div>

                @if($index < count($templates) - 1)
                    <div class="page-gap"></div>
                @endif

            @endif
        @endforeach
    </div>




    <!-- HIDDEN PRINT CONTENT -->
    <div id="print-content" class="hidden">
        @foreach ($templates as $template)
            @if(isset($renderedContent[$template->id]))

                <div class="print-page" style="
                    padding-top: {{ $margins[$template->id]['top'] }}mm;
                    padding-bottom: {{ $margins[$template->id]['bottom'] }}mm;
                    padding-left: {{ $margins[$template->id]['left'] }}mm;
                    padding-right: {{ $margins[$template->id]['right'] }}mm;
                ">
                    <div class="print-document">
                        {!! $renderedContent[$template->id] !!}
                    </div>
                </div>

            @endif
        @endforeach
    </div>



    <!-- PRINT SCRIPT -->
    <script>
        window.prepareAndPrint = function() {
            const rawContent = document.getElementById('print-content').innerHTML;

            const printWindow = window.open('', '_blank');
            const doc = printWindow.document;

            doc.open();
            doc.write('<!DOCTYPE html>');
            doc.write('<html><head>');
            doc.write('<meta charset="utf-8"><title>Print</title>');
            doc.write(`
                <style>
                    @page { size: A4; margin: 0; }
                    body { font-family: "Times New Roman"; font-size: 12pt; }
                    .print-page { page-break-after: always; }
                    .print-page:last-child { page-break-after: auto; }
                </style>
            `);
            doc.write('</head><body>');
            doc.write(rawContent);
            doc.write('</body></html>');
            doc.close();

            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 200);
            };
        };
    </script>


    <!-- STYLES -->
    <style>
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin: 20px auto;
        }

        .print-content {
            font-family: "Times New Roman";
        }

        .page-gap {
            height: 40px;
            background: #f0f0f0;
            margin: 20px auto;
            width: 210mm;
        }

        #print-content { display: none; }
    </style>

</div>
