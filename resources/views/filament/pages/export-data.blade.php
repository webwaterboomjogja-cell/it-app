<x-filament-panels::page>
    <form wire:submit="exportData" class="space-y-6">
        {{ $this->form }}

        <div
            class="
                flex flex-col gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            ">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                File akan dibuat berdasarkan jenis laporan,
                format, dan filter yang dipilih.
            </p>

            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" wire:loading.attr="disabled"
                wire:target="exportData">
                <span wire:loading.remove wire:target="exportData">
                    Download Laporan
                </span>

                <span wire:loading wire:target="exportData">
                    Menyiapkan laporan...
                </span>
            </x-filament::button>
        </div>
        
    </form>
</x-filament-panels::page>
