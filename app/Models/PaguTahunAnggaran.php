<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaguTahunAnggaran extends Model
{
    use HasRoles, HasUuids, SoftDeletes;

    protected $table = 'tb_pagu_tahun_anggaran'; // ✅ beri tahu Laravel nama tabel aslinya

    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'tahun',
        'nominal_dana',
        'status_aktif',
    ];

    public static function getActive()
    {
        return self::where('status_aktif', 1)->first();
    }

    // Enforce: hanya 1 tahun anggaran yang aktif dalam satu waktu
    public static function booted()
    {
        static::saved(function ($model) {
            if ($model->status_aktif) {
                // Set semua baris lain menjadi nonaktif (jangan trigger event lagi dengan update langsung)
                static::where('id', '!=', $model->id)
                    ->where('status_aktif', true)
                    ->update(['status_aktif' => false]);
            }
        });
    }
}
