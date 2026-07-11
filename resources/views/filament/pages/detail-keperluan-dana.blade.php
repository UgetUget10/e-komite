<x-filament-panels::page>
    <div class="dkd-tabs">
        <button
            type="button"
            wire:click="setActiveTab('pagu')"
            class="dkd-tab {{ $activeTab === 'pagu' ? 'dkd-tab-active' : '' }}"
        >
            <x-heroicon-o-banknotes class="h-4 w-4" />
            Keperluan Dana
        </button>

        <button
            type="button"
            wire:click="setActiveTab('realisasi')"
            class="dkd-tab {{ $activeTab === 'realisasi' ? 'dkd-tab-active' : '' }}"
        >
            <x-heroicon-o-chart-bar class="h-4 w-4" />
            Realisasi
        </button>

        <button
            type="button"
            wire:click="setActiveTab('bulanan')"
            class="dkd-tab {{ $activeTab === 'bulanan' ? 'dkd-tab-active' : '' }}"
        >
            <x-heroicon-o-calendar-days class="h-4 w-4" />
            Realisasi per Bulan
        </button>
    </div>

    @php
        $fmt = fn ($n) => $n ? number_format((int)$n, 0, '.', '.') : '-';
    @endphp

    @if ($activeTab === 'bulanan')
        <div class="dkd-card">
            <div class="dkd-table-scroll">
                <table class="dkd-table dkd-table-compact">
                    <thead>
                        <tr>
                            <th rowspan="2" class="dkd-th dkd-th-no">NO.</th>
                            <th rowspan="2" class="dkd-th dkd-th-left">URAIAN ANGGARAN BELANJA</th>
                            <th colspan="12" class="dkd-th">REALISASI PER BULAN{{ $tahunAktif ? ' (' . $tahunAktif->tahun . ')' : '' }}</th>
                            <th rowspan="2" class="dkd-th">JUMLAH (Rp)</th>
                        </tr>
                        <tr>
                            @foreach ($bulanLabels as $label)
                                <th class="dkd-th">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bulananRows as $index => $row)
                            <tr class="dkd-row">
                                <td class="dkd-td dkd-td-center">{{ $index + 1 }}</td>
                                <td class="dkd-td">{{ $row['judul_program'] }}</td>
                                @foreach ($row['bulan'] as $nominalBulan)
                                    <td class="dkd-td dkd-td-right">{{ $fmt($nominalBulan) }}</td>
                                @endforeach
                                <td class="dkd-td dkd-td-right dkd-text-success">{{ $fmt($row['total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="dkd-td dkd-td-center dkd-empty">
                                    Belum ada program kegiatan untuk standar ini{{ $tahunAktif ? ' pada tahun anggaran ' . $tahunAktif->tahun : '' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($bulananRows) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2" class="dkd-td-foot dkd-td-center">JUMLAH</td>
                                @foreach ($totalPerBulan as $totalBulan)
                                    <td class="dkd-td-foot dkd-td-right">{{ $fmt($totalBulan) }}</td>
                                @endforeach
                                <td class="dkd-td-foot dkd-td-right dkd-foot-success">{{ $fmt($totalRealisasiBulanan) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @elseif ($activeTab === 'pagu')
        <div class="dkd-card">
            <div class="dkd-table-scroll">
                <table class="dkd-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="dkd-th dkd-th-no">NO.</th>
                            <th rowspan="2" class="dkd-th dkd-th-left">URAIAN ANGGARAN BELANJA</th>
                            <th colspan="3" class="dkd-th">KEPERLUAN DANA SATU TAHUN</th>
                        </tr>
                        <tr>
                            <th class="dkd-th">DIPA (Rp)</th>
                            <th class="dkd-th">BOS (Rp)</th>
                            <th class="dkd-th">KOMITE (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            <tr class="dkd-row">
                                <td class="dkd-td dkd-td-center">{{ $index + 1 }}</td>
                                <td class="dkd-td">{{ $row['judul_program'] }}</td>
                                <td class="dkd-td dkd-td-right">{{ $fmt($row['pagu_dipa']) }}</td>
                                <td class="dkd-td dkd-td-right">{{ $fmt($row['pagu_bos']) }}</td>
                                <td class="dkd-td dkd-td-right">{{ $fmt($row['pagu_komite']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="dkd-td dkd-td-center dkd-empty">
                                    Belum ada program kegiatan untuk standar ini{{ $tahunAktif ? ' pada tahun anggaran ' . $tahunAktif->tahun : '' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($rows) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2" class="dkd-td-foot dkd-td-center">JUMLAH</td>
                                <td class="dkd-td-foot dkd-td-right">{{ $fmt($totalDipa) }}</td>
                                <td class="dkd-td-foot dkd-td-right">{{ $fmt($totalBos) }}</td>
                                <td class="dkd-td-foot dkd-td-right dkd-foot-highlight">{{ $fmt($totalKomite) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @else
        <div class="dkd-card">
            <div class="dkd-table-scroll">
                <table class="dkd-table">
                    <thead>
                        <tr>
                            <th class="dkd-th dkd-th-no">NO.</th>
                            <th class="dkd-th dkd-th-left">URAIAN ANGGARAN BELANJA</th>
                            <th class="dkd-th">PAGU KOMITE (Rp)</th>
                            <th class="dkd-th">REALISASI (Rp)</th>
                            <th class="dkd-th">SISA ANGGARAN (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($realisasiRows as $index => $row)
                            <tr class="dkd-row">
                                <td class="dkd-td dkd-td-center">{{ $index + 1 }}</td>
                                <td class="dkd-td">{{ $row['judul_program'] }}</td>
                                <td class="dkd-td dkd-td-right">{{ $fmt($row['pagu_komite']) }}</td>
                                <td class="dkd-td dkd-td-right dkd-text-success">{{ $fmt($row['realisasi']) }}</td>
                                <td class="dkd-td dkd-td-right {{ $row['sisa'] < 0 ? 'dkd-text-danger' : '' }}">{{ $fmt($row['sisa']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="dkd-td dkd-td-center dkd-empty">
                                    Belum ada program kegiatan untuk standar ini{{ $tahunAktif ? ' pada tahun anggaran ' . $tahunAktif->tahun : '' }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (count($realisasiRows) > 0)
                        <tfoot>
                            <tr>
                                <td colspan="2" class="dkd-td-foot dkd-td-center">JUMLAH</td>
                                <td class="dkd-td-foot dkd-td-right">{{ $fmt($totalKomiteRealisasi) }}</td>
                                <td class="dkd-td-foot dkd-td-right dkd-foot-success">{{ $fmt($totalRealisasi) }}</td>
                                <td class="dkd-td-foot dkd-td-right {{ $totalSisa < 0 ? 'dkd-text-danger' : '' }}">{{ $fmt($totalSisa) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
