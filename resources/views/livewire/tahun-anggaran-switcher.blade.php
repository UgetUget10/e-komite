<div class="flex items-center justify-end mb-4" x-data="{ open: false }">
    <div class="relative">
        <button
            type="button"
            x-on:click="open = !open"
            x-on:click.outside="open = false"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:shadow-md">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Tahun Anggaran: <span class="font-bold">{{ $selectedTahun ?? '-' }}</span></span>
            <svg class="w-4 h-4 transition-transform" x-bind:class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
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
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
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
