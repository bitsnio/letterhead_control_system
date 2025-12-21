<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $fileName }}
            </h4>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if ($isImage)
                    Image File
                @elseif ($isPDF)
                    PDF Document
                @else
                    Document File
                @endif
            </p>
        </div>

        {{-- Download button --}}
        <a
            href="{{ $fileUrl }}"
            download
            class="fi-btn fi-btn-size-sm fi-btn-color-primary"
        >
            <span class="fi-btn-icon">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            </span>
            <span>Download</span>
        </a>
    </div>

    {{-- Preview --}}
    <div class="rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
        @if ($isImage)
            <img
                src="{{ $fileUrl }}"
                alt="Document Preview"
                class="max-h-[70vh] w-full object-contain"
            >
        @elseif ($isPDF)
            <div class="flex flex-col items-center justify-center gap-3 p-6 text-center">
                <x-heroicon-o-document
                    class="h-8 w-8 text-gray-400"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    PDF preview is not available in the modal.
                </p>
                <a
                    href="{{ $fileUrl }}"
                    target="_blank"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray"
                >
                    <span class="fi-btn-icon">
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    </span>
                    <span>Open PDF</span>
                </a>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 p-6 text-center">
                <x-heroicon-o-document
                    class="h-8 w-8 text-gray-400"
                />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Document preview is not available.
                </p>
                <a
                    href="{{ $fileUrl }}"
                    target="_blank"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray"
                >
                    <span class="fi-btn-icon">
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    </span>
                    <span>Open Document</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Footer note --}}
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Use the download button to save a copy of this document.
    </p>
</div>
