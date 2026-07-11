<x-filament-widgets::widget>
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-main-black">Status Budget Alert</h3>

        @if ($alerts = $this->getAlerts())
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    <div class="rounded-lg border-l-4 p-4" style="border-left-color: {{ $alert['type'] === 'danger' ? '#f44336' : '#ff9800' }}; background: {{ $alert['type'] === 'danger' ? 'rgba(244, 67, 54, 0.05)' : 'rgba(255, 152, 0, 0.05)' }};">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-semibold text-main-black">{{ $alert['title'] }}</p>
                                <p class="text-sm text-main-secondary mt-1">{{ $alert['message'] }}</p>
                            </div>
                            <span class="ml-3 rounded-full px-3 py-1 text-xs font-bold" style="background: {{ $alert['type'] === 'danger' ? 'rgba(244, 67, 54, 0.2); color: #f44336;' : 'rgba(255, 152, 0, 0.2); color: #ff9800;' }}">
                                {{ round($alert['percentage'], 1) }}%
                            </span>
                        </div>

                        <!-- Progress bar -->
                        <div class="mt-3 h-2 w-full rounded-full" style="background: rgba(0, 0, 0, 0.1);">
                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ min($alert['percentage'], 100) }}%; background: {{ $alert['type'] === 'danger' ? '#f44336' : '#ff9800' }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg bg-green-50 p-4 text-center">
                <p class="text-sm font-semibold text-green-700">✓ Semua budget dalam kondisi normal</p>
            </div>
        @endif
    </div>

    <style>
        .text-main-black {
            color: #1a1a1a;
        }

        .text-main-secondary {
            color: #666;
        }
    </style>
</x-filament-widgets::widget>
