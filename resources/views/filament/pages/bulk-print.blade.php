<x-filament-panels::page>
    <div class="space-y-8">

        <form wire:submit="print">

            <x-filament::section
                icon="heroicon-o-cog-6-tooth"
                heading="Print Configuration"
                description="Set quantity, letterhead batch, and variable values."
            >
                {{ $this->form }}

                {{-- FORCE RIGHT JUSTIFY FOOTER --}}
                <x-slot name="footer">
                    <div class="w-full flex justify-end gap-3">

                        {{-- Cancel --}}
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.resources.letterhead-templates.index') }}"
                            color="gray"
                            icon="heroicon-o-x-mark"
                        >
                            Cancel
                        </x-filament::button>

                        {{-- Preview --}}
                        <x-filament::button
                            type="button"
                            color="info"
                            icon="heroicon-o-eye"
                            wire:click="preview"
                        >
                            Preview
                        </x-filament::button>

                        {{-- Print --}}
                        <x-filament::button
                            type="submit"
                            color="primary"
                            icon="heroicon-o-printer"
                        >
                            Print Now
                        </x-filament::button>

                    </div>
                </x-slot>

            </x-filament::section>

        </form>

    </div>
</x-filament-panels::page>
