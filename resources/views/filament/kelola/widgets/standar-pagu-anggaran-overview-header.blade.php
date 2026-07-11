{{-- resources/views/filament/widgets/standar-pagu-overview-header.blade.php --}}
<div class="fi-dashboard-standar-header relative mb-6 flex items-center justify-between gap-4 rounded-2xl border border-black/5 bg-white/60 px-5 py-4 backdrop-blur-sm dark:bg-white/5">
    <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-main-amber/15 text-main-amber">
            <x-heroicon-o-chart-bar class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-lg font-bold leading-tight text-main-black">
                Keperluan Dana per Standar
            </h2>
            <p class="mt-0.5 flex items-center gap-1.5 text-xs text-main-secondary">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-main-amber opacity-75"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-main-amber"></span>
                </span>
                Data real-time &bull; {{ now()->format('d M Y H:i') }}
            </p>
        </div>
    </div>

    <div class="flex gap-2">
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
