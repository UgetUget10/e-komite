<div class="fi-page-header">
    <div class="fi-page-header-top">
        <div class="flex items-center justify-between gap-4 w-full">
            <h1 class="fi-page-heading">Dasbor</h1>
            <div class="flex gap-3">
                @php
                    $headerActions = $this->getCachedHeaderActions();
                @endphp
                @foreach($headerActions as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 border-t border-gray-200 pt-6">
        <div class="quick-actions-grid">
            <a href="{{ $createRealisasiUrl }}" class="quick-action-card quick-action-card--primary">
                <span class="quick-action-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                <span class="quick-action-text">
                    <span class="quick-action-title">Buat Baru</span>
                    <span class="quick-action-subtitle">Realisasi</span>
                </span>
            </a>

            <a href="{{ $realisasiIndexUrl }}" class="quick-action-card quick-action-card--info">
                <span class="quick-action-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="quick-action-text">
                    <span class="quick-action-title">Lihat Laporan</span>
                    <span class="quick-action-subtitle">Realisasi</span>
                </span>
            </a>

            <a href="{{ $programIndexUrl }}" class="quick-action-card quick-action-card--success">
                <span class="quick-action-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="quick-action-text">
                    <span class="quick-action-title">Kelola Program</span>
                    <span class="quick-action-subtitle">Kegiatan</span>
                </span>
            </a>

            <a href="{{ $paguIndexUrl }}" class="quick-action-card quick-action-card--warning">
                <span class="quick-action-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="quick-action-text">
                    <span class="quick-action-title">Atur Anggaran</span>
                    <span class="quick-action-subtitle">Tahun</span>
                </span>
            </a>
        </div>
    </div>
</div>
