<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-warning-50 dark:bg-warning-900/20 p-4 border border-warning-200 dark:border-warning-800">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-200">
                        Action Required
                    </h3>
                    <div class="mt-2 text-sm text-warning-700 dark:text-warning-300">
                        <p>
                            Please review the template carefully before approving or rejecting. Your decision will be recorded in the approval history.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{ $this->infolist }}
    </div>
</x-filament-panels::page>