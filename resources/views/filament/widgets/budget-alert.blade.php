<x-filament-widgets::widget>
    <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-orange-50 dark:bg-orange-900/20">
                    <x-heroicon-o-bell-alert class="w-5 h-5 text-orange-500" />
                </div>
                <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">Status Budget Alert</h3>
            </div>

            @php $alerts = $this->getAlerts(); @endphp
            @if (!empty($alerts))
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400">
                    {{ count($alerts) }} Peringatan
                </span>
            @endif
        </div>

        @if (!empty($alerts))
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    @php
                        $isDanger = $alert['type'] === 'danger';
                    @endphp
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700 p-4 pl-5 transition-all duration-200 hover:shadow-sm
                        {{ $isDanger ? 'bg-red-50/50 dark:bg-red-900/10' : 'bg-orange-50/50 dark:bg-orange-900/10' }}">

                        <span class="absolute left-0 top-0 h-full w-1 {{ $isDanger ? 'bg-red-500' : 'bg-orange-500' }}"></span>

                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0 {{ $isDanger ? 'text-red-500' : 'text-orange-500' }}" />
                                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">{{ $alert['title'] }}</p>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">{{ $alert['message'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $isDanger ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                {{ round($alert['percentage'], 1) }}%
                            </span>
                        </div>

                        <div class="mt-3 h-1.5 w-full rounded-full bg-gray-200/70 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $isDanger ? 'bg-red-500' : 'bg-orange-500' }}"
                                style="width: {{ min($alert['percentage'], 100) }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/10 p-6 text-center">
                <x-heroicon-o-check-circle class="w-10 h-10 text-emerald-500 mx-auto mb-2" />
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Semua budget dalam kondisi normal</p>
                <p class="text-xs text-emerald-600/70 dark:text-emerald-500/70 mt-1">Tidak ada program yang mendekati atau melebihi batas anggaran</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
