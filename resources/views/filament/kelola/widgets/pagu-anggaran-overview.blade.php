@props(['stats'])

<div class="grid gap-6 md:grid-cols-3">

    {{-- Total Pendapatan --}}
    <div
        class="p-6 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Pendapatan {{ $stats['tahun'] }}
            </p>
            <x-heroicon-o-banknotes class="w-6 h-6 text-emerald-500" />
        </div>

        <p class="mt-3 text-3xl font-bold text-gray-800 dark:text-gray-200">
            Rp {{ number_format($stats['totalPagu'], 0, ',', '.') }}
        </p>

        <div class="mt-2 flex items-center space-x-2">
            @if ($stats['isAktif'])
                <x-heroicon-s-check-circle class="w-5 h-5 text-green-600" />
                <span class="text-green-600 text-sm">Aktif</span>
            @else
                <x-heroicon-s-x-circle class="w-5 h-5 text-red-600" />
                <span class="text-red-600 text-sm">Tidak Aktif</span>
            @endif
        </div>
    </div>

    {{-- Total Keperluan Dana --}}
    <div
        class="p-6 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Keperluan Dana</h3>
            <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-indigo-500" />
        </div>

        <p class="mt-3 text-3xl font-bold text-gray-800 dark:text-gray-200">
            Rp {{ number_format($stats['paguDiajukan'], 0, ',', '.') }}
        </p>

        <div class="mt-2 flex items-center space-x-2">
            <x-dynamic-component :component="$stats['icon']" class="w-5 h-5 text-blue-600" />
            <span class="text-blue-600 text-sm">{{ $stats['desc'] }}</span>
        </div>
    </div>

    {{-- Sisa Anggaran --}}
    <div
        class="p-6 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Sisa Anggaran</h3>

            @php
                $sisa = $stats['sisaPagu'];
                $icon =
                    $sisa < 0
                        ? 'heroicon-s-exclamation-triangle'
                        : ($sisa == 0
                            ? 'heroicon-s-minus-circle'
                            : 'heroicon-s-banknotes');
                $color = $sisa < 0 ? 'text-red-500' : ($sisa == 0 ? 'text-yellow-500' : 'text-emerald-500');
            @endphp

            <x-dynamic-component :component="$icon" class="w-6 h-6 {{ $color }}" />
        </div>

        <p class="{{ $sisa < 0 ? 'text-red-600' : 'text-gray-800 dark:text-gray-200' }} mt-3 text-3xl font-bold">
            Rp {{ number_format($sisa, 0, ',', '.') }}
        </p>

        <div class="mt-2 flex items-center space-x-2">
            @if ($sisa < 0)
                <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-red-600" />
                <span class="text-red-600 text-sm">Melebihi Anggaran!</span>
            @elseif($sisa == 0)
                <x-heroicon-s-minus-circle class="w-5 h-5 text-yellow-500" />
                <span class="text-yellow-600 text-sm">Habis</span>
            @else
                <x-heroicon-s-banknotes class="w-5 h-5 text-emerald-600" />
                <span class="text-emerald-600 text-sm">Tersedia</span>
            @endif
        </div>
    </div>

</div>
