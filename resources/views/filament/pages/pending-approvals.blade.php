<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-primary-50 dark:bg-primary-900/20 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-filament::icon 
                        icon="heroicon-o-information-circle" 
                        class="h-5 w-5 text-primary-600 dark:text-primary-400" 
                    />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-primary-800 dark:text-primary-200">
                        Review Required
                    </h3>
                    <div class="mt-2 text-sm text-primary-700 dark:text-primary-300">
                        <p>
                            The following templates are waiting for your approval. Click "Review" to view the template details and take action.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>