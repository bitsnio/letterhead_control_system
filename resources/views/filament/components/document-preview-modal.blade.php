<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $fileName }}
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $isImage ? 'Image Preview' : ($isPDF ? 'PDF Document' : 'File') }}
            </p>
        </div>
        <x-filament::button
            tag="a"
            href="{{ $fileUrl }}"
            download="{{ $fileName }}"
            target="_blank"
            size="sm"
            icon="heroicon-m-arrow-down-tray"
            iconSize="sm"
        >
            Download
        </x-filament::button>
    </div>

    {{-- Preview Area --}}
    <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
        @if ($isImage)
            <div class="flex justify-center p-4">
                <img 
                    src="{{ $fileUrl }}" 
                    alt="Preview" 
                    class="max-h-[60vh] w-auto object-contain rounded shadow-sm"
                >
            </div>
        @elseif ($isPDF)
            <div class="space-y-2">
                {{-- Embed PDF via Iframe --}}
                <iframe 
                    src="{{ $fileUrl }}#toolbar=1&view=FitH" 
                    class="w-full h-[60vh]" 
                    frameborder="0"
                >
                    <p>Your browser does not support PDFs. 
                        <a href="{{ $fileUrl }}" download="{{ $fileName }}">Download instead</a>.
                    </p>
                </iframe>
            </div>
        @else
            {{-- Generic File Placeholder --}}
            <div class="flex flex-col items-center justify-center gap-3 p-12 text-center">
                <x-filament::icon
                    icon="heroicon-o-document-text"
                    class="h-16 w-16 text-gray-300 dark:text-gray-600"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Preview not supported for this file type.
                </p>
                <x-filament::button
                    tag="a"
                    href="{{ $fileUrl }}"
                    download="{{ $fileName }}"
                    target="_blank"
                    size="sm"
                    icon="heroicon-m-arrow-down-tray"
                >
                    Download File
                </x-filament::button>
            </div>
        @endif
    </div>

    {{-- Helper Note --}}
    <p class="text-xs text-gray-400 text-center">
        If the file doesn't load, please check your storage permissions or try downloading.
    </p>
</div>