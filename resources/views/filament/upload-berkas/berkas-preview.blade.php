@once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>
@endonce

<div
    x-data="{
        url: @js(($existingIsPdf ?? false) ? $existingUrl : null),
        blobUrl: null,
        status: 'idle', // idle | loading | ready | error
        renderToken: 0,

        init() {
            const form = this.$el.closest('form');

            if (this.url) {
                this.renderPdf(this.url);
            }

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
                    this.status = 'idle';
                    return;
                }

                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');

                if (! isPdf) {
                    this.url = null;
                    this.status = 'idle';
                    return;
                }

                this.blobUrl = URL.createObjectURL(file);
                this.url = this.blobUrl;
                this.renderPdf(this.url);
            });
        },

        async renderPdf(url) {
            const token = ++this.renderToken;
            this.status = 'loading';

            const container = this.$refs.pages;
            if (container) {
                container.innerHTML = '';
            }

            if (! window.pdfjsLib) {
                this.status = 'error';
                return;
            }

            try {
                const loadingTask = window.pdfjsLib.getDocument(url);
                const pdf = await loadingTask.promise;

                // Batalkan render lama jika url berganti lagi sebelum selesai (mis. user ganti file cepat-cepat)
                if (token !== this.renderToken) {
                    return;
                }

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    const page = await pdf.getPage(pageNum);

                    if (token !== this.renderToken) {
                        return;
                    }

                    const containerWidth = container?.clientWidth || 800;
                    const unscaledViewport = page.getViewport({ scale: 1 });
                    const scale = containerWidth / unscaledViewport.width;
                    const viewport = page.getViewport({ scale });

                    const canvas = document.createElement('canvas');
                    canvas.className = 'upload-berkas-preview-page';
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;

                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport }).promise;

                    container?.appendChild(canvas);
                }

                this.status = 'ready';
            } catch (e) {
                console.error('Gagal render preview PDF:', e);
                this.status = 'error';
            }
        },
    }"
>
    <template x-if="url">
        <div>
            <div class="upload-berkas-preview-toolbar">
                <a x-bind:href="url" target="_blank" rel="noopener" class="upload-berkas-preview-open-link">
                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    Buka di tab baru
                </a>
            </div>

            <div x-show="status === 'loading'" class="upload-berkas-preview-loading">
                <x-heroicon-o-arrow-path class="h-5 w-5 animate-spin" />
                Memuat pratinjau…
            </div>

            <div x-show="status === 'error'" x-cloak class="upload-berkas-preview-fallback">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-500" />
                <span>Gagal memuat pratinjau. Gunakan tombol "Buka di tab baru" di atas untuk melihat berkas.</span>
            </div>

            <div
                x-ref="pages"
                x-show="status === 'ready'"
                x-cloak
                class="upload-berkas-preview-pages"
            ></div>
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
