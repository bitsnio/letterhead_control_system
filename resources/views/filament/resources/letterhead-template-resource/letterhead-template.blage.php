<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg bg-info-50 dark:bg-info-900/20 p-4 border border-info-200 dark:border-info-800">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-info-600 dark:text-info-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-info-800 dark:text-info-200">
                        Fill in Template Variables
                    </h3>
                    <div class="mt-2 text-sm text-info-700 dark:text-info-300">
                        <p>
                            Enter values for all variables below. Once completed, click "Generate Print" to preview your document.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Template Information
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-filament::section.description class="font-semibold">
                        Template Name
                    </x-filament::section.description>
                    <p class="mt-1">{{ $record->name }}</p>
                </div>

                <div>
                    <x-filament::section.description class="font-semibold">
                        Category
                    </x-filament::section.description>
                    <p class="mt-1">
                        <x-filament::badge>
                            {{ ucfirst($record->category ?? 'N/A') }}
                        </x-filament::badge>
                    </p>
                </div>

                @if($record->description)
                <div class="col-span-2">
                    <x-filament::section.description class="font-semibold">
                        Description
                    </x-filament::section.description>
                    <p class="mt-1">{{ $record->description }}</p>
                </div>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Variable Values
            </x-slot>

            <form wire:submit="generatePrint" class="space-y-4">
                @if(!empty($record->variables))
                    @foreach($record->variables as $variable)
                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="text"
                                    wire:model="variableValues.{{ $variable }}"
                                    placeholder="Enter value for ${{ $variable }}$"
                                />
                            </x-filament::input.wrapper>
                            <x-filament::section.description class="mt-1">
                                Variable: <span class="font-mono text-info-600 dark:text-info-400">${{ $variable }}$</span>
                            </x-filament::section.description>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        This template has no variables.
                    </p>
                @endif

                <div class="flex justify-end gap-3 pt-4">
                    <x-filament::button
                        type="submit"
                        color="primary"
                        icon="heroicon-o-printer"
                    >
                        Generate Print
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Template Preview
            </x-slot>

            <div class="prose dark:prose-invert max-w-none p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                {!! $record->content !!}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>