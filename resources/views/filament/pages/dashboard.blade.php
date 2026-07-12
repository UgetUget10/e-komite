<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header dengan Tahun Switcher -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan anggaran dan realisasi</p>
            </div>
            <livewire:tahun-anggaran-switcher />
        </div>

        <!-- Dashboard widgets akan di-render di bawah -->
    </div>
</x-filament-panels::page>
