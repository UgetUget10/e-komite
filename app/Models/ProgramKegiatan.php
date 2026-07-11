<?php

namespace App\Models;

use App\Models\Standar;
use App\Models\PaguTahunAnggaran;
use App\Models\RealisasiNominal;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProgramKegiatan extends Model
{
    use HasUuids;

    protected $table = 'tb_program_kegiatan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_standar',
        'judul_program',
        'kode',
        'kode_lengkap',
        'pagu_dipa',
        'pagu_bos',
        'pagu_swadaya',
        'pagu_komite',
        'total_pengeluaran_perprogram ',
        'sisa_anggaran_perprogram ',
    ];

    // <<< WAJIB DITAMBAHKAN >>>
    // protected $casts = [
    //     'pagu_dipa'   => 'integer',
    //     'pagu_komite' => 'integer',
    //     'total'       => 'integer',
    // ];

    // Cast semua kolom uang ke decimal agar presisi tetap terjaga
    protected $casts = [
        'pagu_dipa' => 'decimal:2',
        'pagu_bos' => 'decimal:2',
        'pagu_swadaya' => 'decimal:2',
        'pagu_komite' => 'decimal:2',
        'total_pengeluaran_perprogram' => 'decimal:2',
        'sisa_anggaran_perprogram' => 'decimal:2',
    ];
    // <<< END >>>

    public function standar()
    {
        return $this->belongsTo(Standar::class, 'id_standar', 'id');
    }

    public function uraianProgram()
    {
        return $this->hasMany(UraianProgram::class, 'id_program_kegiatan');
    }

    public function realisasi()
    {
        return $this->hasMany(RealisasiNominal::class, 'fk_id_program_kegiatan', 'id');
    }

    protected static function booted()
    {
        static::saved(function ($program) {
            $program->hitungTotalUraian();
        });

        static::deleting(function ($program) {
            $program->uraianProgram()->delete();
        });

        static::creating(function ($program) {
            $pagu = PaguTahunAnggaran::where('status_aktif', 1)->first();
            if ($pagu) {
                $program->fk_id_pagu_tahun_anggaran = $pagu->id;
            }
        });
    }

    public function hitungTotalUraian(): void
    {
        $this->total = $this->uraianProgram()->sum('jumlah');
        $this->saveQuietly();
    }
}
