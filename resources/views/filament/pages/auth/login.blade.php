@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
@endphp

<div>
    <div class="fi-auth-split-screen">
        {{-- Kiri: panel branding --}}
        <div class="fi-auth-split-branding">
            <div class="fi-auth-split-branding-pattern" aria-hidden="true"></div>

            <div class="fi-auth-split-branding-content">
                <span class="fi-auth-split-brand-wordmark">KOMITE</span>

                <p class="fi-auth-split-brand-tagline">
                    Sistem Informasi Pengelolaan Komite Madrasah &mdash;
                    transparan, akuntabel, dan terintegrasi dalam satu platform.
                </p>

                <ul class="fi-auth-split-brand-highlights">
                    <li>Pengelolaan anggaran &amp; realisasi yang terukur</li>
                    <li>Pelaporan real-time untuk pemangku kepentingan</li>
                    <li>Akses aman berbasis peran pengguna</li>
                </ul>
            </div>
        </div>

        {{-- Kanan: card form login --}}
        <div class="fi-auth-split-form-ctn">
            <div class="fi-auth-split-form-decor fi-auth-split-form-decor-1" aria-hidden="true"></div>
            <div class="fi-auth-split-form-decor fi-auth-split-form-decor-2" aria-hidden="true"></div>
            <div class="fi-auth-split-form-pattern" aria-hidden="true"></div>

            <div class="fi-auth-split-form-wrap">
                <div class="fi-auth-split-form-card">
                    @if (filled($heading) || filled($subheading))
                        <x-filament-panels::header.simple :heading="$heading" :logo="false" :subheading="$subheading"
                            class="-mt-5" />
                    @endif

                    {{ $this->content }}
                </div>

                <p class="fi-auth-split-form-footer">
                    &copy; {{ date('Y') }} KOMITE &mdash; Sistem Informasi Pengelolaan Komite Madrasah
                </p>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
