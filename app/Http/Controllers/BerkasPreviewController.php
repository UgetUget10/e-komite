<?php

namespace App\Http\Controllers;

use App\Models\UploadBerkas;
use Illuminate\Support\Facades\Storage;

class BerkasPreviewController extends Controller
{
    public function __invoke(UploadBerkas $uploadBerkas)
    {
        abort_unless($uploadBerkas->berkas && Storage::disk('public')->exists($uploadBerkas->berkas), 404);

        return Storage::disk('public')->response(
            $uploadBerkas->berkas,
            basename($uploadBerkas->berkas),
            [],
            'inline',
        );
    }
}
