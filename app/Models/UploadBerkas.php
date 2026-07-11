<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UploadBerkas extends Model
{
    use HasUuids;

    protected $table = 'tb_upload_berkas';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'judul',
        'keterangan',
        'tanggal',
        'berkas',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}
