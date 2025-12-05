@php
$serialUsage = $getRecord() ?? $serialUsage;
$template = $serialUsage->template;
$printJob = $serialUsage->printJob;
$variableData = $printJob->variable_data ?? [];
$templateVariables = $variableData[$template->id] ?? [];
@endphp

<div class="p-6 bg-white rounded-lg">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Template Preview</h3>
        <p class="text-sm text-gray-600">
            Serial: <strong>{{ number_format($serialUsage->serial_number) }}</strong> |
            Job: <strong>#{{ $printJob->id }}</strong> |
            Template: <strong>{{ $template->name }}</strong>
        </p>
    </div>

    <div class="space-y-6">
        <div class="border rounded-lg p-4 bg-gray-50">
            <h4 class="font-semibold text-gray-800 mb-2">{{ $template->name }}</h4>
            <div class="print-content max-h-96 overflow-y-auto">
                @php
                $content = $template->content ?? '';

                // Replace variables in content
                foreach ($templateVariables as $variable => $value) {
                $content = str_replace(
                [
                '$' . $variable . '$',
                '{' . $variable . '}',
                '{{' . $variable . '}}',
                '{$' . $variable . '$}'
                ],
                $value,
                $content
                );
                }

                // Also replace serial number variable if present
                $content = str_replace(
                ['$serial$', '{serial}', '{{serial}}', '{$serial$}'],
                $serialUsage->serial_number,
                $content
                );
                @endphp
                {!! $content !!}
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end space-x-3">
        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.printed-letterheads.edit', $serialUsage->id) }}"
            color="primary"
            icon="heroicon-o-pencil">
            Edit Serial
        </x-filament::button>

        <x-filament::button
            wire:click="$dispatch('close-modal')"
            color="gray">
            Close
        </x-filament::button>
    </div>
</div>

<style>
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
</style>