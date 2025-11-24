{{-- resources/views/filament/widgets/standar-pagu-overview.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
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

                {{--  <x-filament::button size="sm" color="success" icon="heroicon-o-arrow-down-tray" :href="route('pagu.export', ['tahun' => $tahunAktifId ?? ''])"
                    target="_blank">
                    Export Excel
                </x-filament::button>  --}}
            </div>
        </div>

        <!-- PERBAIKAN: GUNAKAN $cards, BUKAN $this->getCards() -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach ($cards as $card)
                {{ $card }}
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
