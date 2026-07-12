<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-main-black mb-2">Laporan Budget Tahunan</h2>
            <p class="text-sm text-main-secondary">Tahun {{ $this->laporanData['tahun'] ?? '-' }}</p>
            <p class="text-xs text-main-secondary">Generate: {{ $this->laporanData['generated_at'] ?? '-' }}</p>
        </div>

        <!-- Filter & Search -->
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search Program -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari Program</label>
                    <input type="text"
                        wire:model.live="searchProgram"
                        placeholder="Ketik judul program..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>

                <!-- Filter Standar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Standar</label>
                    <select wire:model.live="selectedStandars" multiple
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        @foreach ($this->standarOptions as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd untuk pilih multiple</p>
                </div>

                <!-- View Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tampilan</label>
                    <select wire:model.live="viewType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="program">Per Program</option>
                        <option value="standar">Per Standar</option>
                        <option value="bulan">Per Bulan</option>
                    </select>
                </div>
            </div>
        </div>

        @if (!empty($this->laporanData['programs']))
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), transparent);">
                    <p class="text-xs font-semibold text-main-secondary mb-1">TOTAL PAGU KOMITE</p>
                    <p class="text-2xl font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pagu'], 0, '.', '.') }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), transparent);">
                    <p class="text-xs font-semibold text-main-secondary mb-1">TOTAL REALISASI</p>
                    <p class="text-2xl font-bold text-main-black">Rp {{ number_format($this->laporanData['total_realisasi'], 0, '.', '.') }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.05), transparent);">
                    <p class="text-xs font-semibold text-main-secondary mb-1">PENGAJUAN BERJALAN</p>
                    <p class="text-2xl font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pengajuan_berjalan'], 0, '.', '.') }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), transparent);">
                    <p class="text-xs font-semibold text-main-secondary mb-1">PENGEMBALIAN</p>
                    <p class="text-2xl font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pengembalian'], 0, '.', '.') }}</p>
                </div>

                <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), transparent);">
                    <p class="text-xs font-semibold text-main-secondary mb-1">EXECUTION RATE</p>
                    <p class="text-2xl font-bold text-main-black">
                        {{ $this->laporanData['total_pagu'] > 0 ? round(($this->laporanData['total_realisasi'] / $this->laporanData['total_pagu']) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>

            <!-- Breakdown By Standar -->
            @if ($this->viewType === 'standar' && !empty($this->breakdownByStandar))
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-amber-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-main-black">Rekapitulasi per Standar</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr style="background: linear-gradient(135deg, rgba(40, 43, 47, 0.03), rgba(255, 152, 0, 0.05));">
                                    <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-bold text-main-black">STANDAR</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">JUMLAH PROGRAM</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">TOTAL PAGU (Rp)</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">TOTAL REALISASI (Rp)</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">PENGAJUAN BERJALAN (Rp)</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">PENGEMBALIAN (Rp)</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">SISA (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->breakdownByStandar as $standar => $data)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-main-black font-medium">{{ $standar }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ $data['count'] }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($data['pagu'], 0, '.', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($data['realisasi'], 0, '.', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($data['pengajuan_berjalan'], 0, '.', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($data['pengembalian'], 0, '.', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-sm {{ $data['sisa'] < 0 ? 'text-red-600 font-bold' : 'text-main-black' }}">{{ number_format($data['sisa'], 0, '.', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Breakdown By Bulan -->
            @if ($this->viewType === 'bulan' && !empty($this->breakdownByBulan))
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-green-50 px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-main-black">Rekapitulasi per Bulan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr style="background: linear-gradient(135deg, rgba(40, 43, 47, 0.03), rgba(255, 152, 0, 0.05));">
                                    <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-bold text-main-black">BULAN</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">JUMLAH TRANSAKSI</th>
                                    <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">TOTAL NOMINAL (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->breakdownByBulan as $bulan => $data)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-main-black font-medium">{{ $bulan }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ $data['count'] }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($data['nominal'], 0, '.', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Table -->
            @if ($this->viewType === 'program')
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full">
                    <thead>
                        <tr style="background: linear-gradient(135deg, rgba(40, 43, 47, 0.03), rgba(255, 152, 0, 0.05));">
                            <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-bold text-main-black">NO</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-bold text-main-black">STANDAR</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-left text-xs font-bold text-main-black">PROGRAM KEGIATAN</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">PAGU (Rp)</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">REALISASI (Rp)</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">PENGAJUAN BERJALAN (Rp)</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">PENGEMBALIAN (Rp)</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-right text-xs font-bold text-main-black">SISA (Rp)</th>
                            <th class="border-b border-gray-200 px-4 py-3 text-center text-xs font-bold text-main-black">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->laporanData['programs'] as $index => $program)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-main-black">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm text-main-black font-medium">{{ $program['standar'] }}</td>
                                <td class="px-4 py-3 text-sm text-main-black">{{ $program['judul'] }}</td>
                                <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($program['pagu'], 0, '.', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-main-black font-medium">{{ number_format($program['realisasi'], 0, '.', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($program['pengajuan_berjalan'], 0, '.', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-main-black">{{ number_format($program['pengembalian'], 0, '.', '.') }}</td>
                                <td class="px-4 py-3 text-right text-sm {{ $program['sisa'] < 0 ? 'text-red-600 font-bold' : 'text-main-black' }}">{{ number_format($program['sisa'], 0, '.', '.') }}</td>
                                <td class="px-4 py-3 text-center text-sm">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                                        style="background: {{ $program['percentage'] >= 100 ? 'rgba(244, 67, 54, 0.2); color: #f44336;' : ($program['percentage'] >= 80 ? 'rgba(255, 152, 0, 0.2); color: #ff9800;' : 'rgba(76, 175, 80, 0.2); color: #4CAF50;') }}">
                                        {{ round($program['percentage'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Footer Summary -->
            <div class="rounded-lg border border-gray-200 p-4" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.05), transparent);">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <p class="text-xs text-main-secondary mb-1">TOTAL PAGU</p>
                        <p class="text-lg font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pagu'], 0, '.', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-main-secondary mb-1">TOTAL REALISASI</p>
                        <p class="text-lg font-bold text-main-black">Rp {{ number_format($this->laporanData['total_realisasi'], 0, '.', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-main-secondary mb-1">PENGAJUAN BERJALAN</p>
                        <p class="text-lg font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pengajuan_berjalan'], 0, '.', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-main-secondary mb-1">PENGEMBALIAN</p>
                        <p class="text-lg font-bold text-main-black">Rp {{ number_format($this->laporanData['total_pengembalian'], 0, '.', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-main-secondary mb-1">SISA ANGGARAN</p>
                        <p class="text-lg font-bold {{ $this->laporanData['total_sisa'] < 0 ? 'text-red-600' : 'text-main-black' }}">Rp {{ number_format($this->laporanData['total_sisa'], 0, '.', '.') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-8 text-center">
                <p class="text-sm text-main-secondary">Tidak ada data untuk tahun ini</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
