<x-filament-widgets::widget>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-main-black">Budget vs Realisasi - Trend Bulanan</h3>
        </div>

        <!-- Filter Controls -->
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Chart Type Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Chart</label>
                    <select wire:model.live="chartType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500">
                        <option value="line">Line Chart</option>
                        <option value="bar">Bar Chart</option>
                        <option value="column">Column Chart</option>
                        <option value="area">Area Chart</option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select wire:model.live="selectedYear" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500">
                        @foreach ($this->tahunList as $tahun)
                            <option value="{{ $tahun }}">{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Filter (Optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Bulan (Opsional)</label>
                    <select wire:model.live="selectedMonth" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500">
                        <option value="0">Semua Bulan</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="rounded-lg border border-gray-200 p-4 bg-white">
            @if (!empty($this->chartData['labels']))
                <canvas id="budgetVsRealisasiChart" style="max-height: 350px;"></canvas>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        renderBudgetChart(@json($this->chartData), '{{ $this->chartType }}');
                    });

                    function renderBudgetChart(data, chartType) {
                        const ctx = document.getElementById('budgetVsRealisasiChart').getContext('2d');

                        // Destroy existing chart if any
                        if (window.budgetChartInstance) {
                            window.budgetChartInstance.destroy();
                        }

                        const chartConfig = {
                            line: {
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                backgroundColor: (context) => {
                                    if (context.datasetIndex === 0) {
                                        return 'rgba(255, 152, 0, 0.1)';
                                    }
                                    return 'rgba(76, 175, 80, 0.1)';
                                },
                            },
                            bar: {
                                borderWidth: 0,
                                backgroundColor: (context) => {
                                    return context.datasetIndex === 0 ? '#FF9800' : '#4CAF50';
                                },
                            },
                            column: {
                                borderWidth: 0,
                                backgroundColor: (context) => {
                                    return context.datasetIndex === 0 ? '#FF9800' : '#4CAF50';
                                },
                            },
                            area: {
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                backgroundColor: (context) => {
                                    if (context.datasetIndex === 0) {
                                        return 'rgba(255, 152, 0, 0.3)';
                                    }
                                    return 'rgba(76, 175, 80, 0.3)';
                                },
                            },
                        };

                        const config = chartConfig[chartType] || chartConfig.line;

                        window.budgetChartInstance = new Chart(ctx, {
                            type: chartType === 'area' ? 'line' : (chartType === 'column' ? 'bar' : chartType),
                            data: {
                                labels: data.labels,
                                datasets: [
                                    {
                                        label: data.datasets[0].label,
                                        data: data.datasets[0].data,
                                        borderColor: '#FF9800',
                                        ...config,
                                    },
                                    {
                                        label: data.datasets[1].label,
                                        data: data.datasets[1].data,
                                        borderColor: '#4CAF50',
                                        ...config,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                            },
                                        },
                                    },
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + new Intl.NumberFormat('id-ID', {notation: 'compact'}).format(value);
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    }
                </script>
            @else
                <div class="text-center py-8">
                    <p class="text-sm text-gray-500">Tidak ada data untuk periode yang dipilih</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
