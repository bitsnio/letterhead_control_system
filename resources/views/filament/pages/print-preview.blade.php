<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-8">
            <div class="print-content">
                {!! $content !!}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('print-document', () => {
                window.print();
            });
        });
    </script>
    @endpush

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-content, .print-content * {
                visibility: visible;
            }
            .print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</x-filament-panels::page>