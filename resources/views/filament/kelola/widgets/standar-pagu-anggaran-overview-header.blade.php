{{-- resources/views/filament/widgets/standar-pagu-overview-header.blade.php --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-950 dark:text-white">
            Ringkasan Pagu Anggaran SPMI
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Data real-time • {{ now()->format('d M Y H:i') }}
        </p>
    </div>

    <div class="flex gap-3">
        <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-path" wire:click="$refresh"
            wire:loading.attr="disabled">
            Refresh
        </x-filament::button>

        <x-filament::button size="sm" color="success" icon="heroicon-o-arrow-down-tray" :href="route('pagu.export', ['tahun' => $tahunAktifId ?? ''])"
            target="_blank">
            Export Excel
        </x-filament::button>
    </div>
</div>
