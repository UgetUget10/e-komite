<x-filament-widgets::widget>
    <div x-data="{ open: false }" class="flex items-start justify-between flex-wrap gap-4">
        <div class="space-y-1.5">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Dasbor</h2>

            <div class="flex items-center flex-wrap gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan data untuk tahun anggaran
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $selectedTahun ?? '-' }}</span>
                </p>

                @unless ($isActiveTahun)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-xs font-semibold leading-none">
                        <x-heroicon-s-exclamation-triangle class="w-3 h-3 shrink-0" />
                        Data historis
                    </span>
                @endunless
            </div>
        </div>

        <div class="relative">
            <button
                type="button"
                x-on:click="open = !open"
                x-on:click.outside="open = false"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:shadow-md">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-amber-500 shrink-0" />
                <span>Tahun <span class="font-bold">{{ $selectedTahun ?? '-' }}</span></span>
                <x-heroicon-s-chevron-down class="w-4 h-4 shrink-0 transition-transform" x-bind:class="{ 'rotate-180': open }" />
            </button>

            <!-- Dropdown Menu -->
            <div
                x-show="open"
                x-transition
                x-cloak
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 py-1">
                @forelse ($tahunList as $tahunId => $tahun)
                    <button
                        type="button"
                        wire:click="switchTahun({{ $tahun }})"
                        x-on:click="open = false"
                        class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors flex items-center gap-2 {{ $selectedTahun == $tahun ? 'bg-amber-50 dark:bg-amber-900/20 border-l-2 border-amber-500 text-amber-600 dark:text-amber-400 font-medium' : '' }}">
                        @if ($selectedTahun == $tahun)
                            <x-heroicon-s-check class="w-4 h-4 shrink-0" />
                        @else
                            <span class="w-4 shrink-0"></span>
                        @endif
                        {{ $tahun }}
                    </button>
                @empty
                    <div class="px-4 py-2 text-gray-500 dark:text-gray-400 text-sm">
                        Tidak ada tahun anggaran
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
