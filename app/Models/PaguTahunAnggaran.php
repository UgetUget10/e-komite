<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaguTahunAnggaran extends Model
{
    use HasRoles, HasUuids;

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
}
