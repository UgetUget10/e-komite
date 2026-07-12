<div
    x-data="{
        url: @js(($existingIsPdf ?? false) ? $existingUrl : null),
        blobUrl: null,
        init() {
            const form = this.$el.closest('form');

            if (! form) {
                return;
            }

            // Delegate on the stable <form> ancestor instead of the file input
            // itself, since FilePond/Livewire may replace the input element
            // after this component initializes.
            form.addEventListener('change', (event) => {
                if (! event.target.matches('input[type=file]')) {
                    return;
                }

                if (this.blobUrl) {
                    URL.revokeObjectURL(this.blobUrl);
                    this.blobUrl = null;
                }

                const file = event.target.files && event.target.files[0];

                if (! file) {
                    this.url = null;

                    return;
                }

                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

                if (! isPdf) {
                    this.url = null;

                    return;
                }

                this.blobUrl = URL.createObjectURL(file);
                this.url = this.blobUrl;
            });
        },
    }"
>
    <template x-if="url">
        <div>
            {{-- Link "buka di tab baru" selalu tampil berdampingan dengan iframe — beberapa
                 browser dikonfigurasi untuk selalu mengunduh PDF alih-alih menampilkannya
                 inline, sehingga iframe bisa terlihat kosong tanpa link ini sebagai jalan keluar. --}}
            <div class="upload-berkas-preview-toolbar">
                <a x-bind:href="url" target="_blank" rel="noopener" class="upload-berkas-preview-open-link">
                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    Buka di tab baru
                </a>
            </div>
            <iframe
                :src="url"
                class="upload-berkas-preview-frame"
                title="Pratinjau berkas"
            ></iframe>
        </div>
    </template>

    @if ($showFallbackLink ?? false)
        <template x-if="!url">
            <div class="upload-berkas-preview-fallback">
                @if ($existingUrl)
                    <x-heroicon-o-document-arrow-down class="h-5 w-5 text-primary-600" />
                    <a href="{{ $existingUrl }}" target="_blank" rel="noopener" class="font-medium text-primary-600 underline">
                        Pratinjau tidak tersedia untuk jenis berkas ini — buka / unduh berkas
                    </a>
                @else
                    <p class="text-gray-500 dark:text-gray-400">Belum ada berkas.</p>
                @endif
            </div>
        </template>
    @endif
</div>
