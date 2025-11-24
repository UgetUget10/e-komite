<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UraianProgram extends Model
{
    use HasRoles, HasUuids;

    protected $table = 'tb_uraian_program'; // ✅ beri tahu Laravel nama tabel aslinya

    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'id_standar',
        'judul_program',
        'id_program_kegiatan',
        'nama_uraian',
        'banyaknya',
        'satuan',
        // 'jumlah',
    ];


    protected static function booted()
    {
        static::saving(function ($uraian) {
            // Pastikan jumlah dihitung dari banyaknya dan satuan setiap kali disimpan
            $uraian->jumlah = (float) $uraian->banyaknya * (float) $uraian->satuan;

            // Pastikan id_standar ikut otomatis dari relasi program
            if ($uraian->programKegiatan) {
                $uraian->id_standar = $uraian->programKegiatan->id_standar;
            }
        });

        // ✅ Saat uraian dibuat, diupdate, atau dihapus, otomatis update total di program terkait
        static::saved(function ($uraian) {
            if ($uraian->programKegiatan) {
                $uraian->programKegiatan->hitungTotalUraian();
            }
        });

        static::deleted(function ($uraian) {
            if ($uraian->programKegiatan) {
                $uraian->programKegiatan->hitungTotalUraian();
            }
        });
    }

    public function programKegiatan()
    {
        return $this->belongsTo(ProgramKegiatan::class, 'id_program_kegiatan');
    }

    public function standars()
    {
        return $this->belongsTo(Standar::class, 'id_standar');
    }
}
