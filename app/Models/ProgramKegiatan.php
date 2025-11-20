<?php

namespace App\Models;

use App\Models\Standar;
use App\Models\PaguTahunAnggaran;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProgramKegiatan extends Model
{
    use HasRoles, HasUuids;

    protected $table = 'tb_program_kegiatan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_standar',
        'judul_program',
        'pagu_dipa',
        'pagu_komite',
    ];

    // <<< WAJIB DITAMBAHKAN >>>
    protected $casts = [
        'pagu_dipa'   => 'integer',
        'pagu_komite' => 'integer',
        'total'       => 'integer',
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