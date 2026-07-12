<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RealisasiNominal extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'tb_realisasi_nominal';   // Nama tabel
    protected $keyType = 'string';               // Primary key berbasis UUID
    public $incrementing = false;                // Tidak auto-increment

    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_BERJALAN = 'berjalan';
    const STATUS_SELESAI = 'selesai';

    const STATUSES = [
        self::STATUS_DIAJUKAN => 'Diajukan',
        self::STATUS_BERJALAN => 'Berjalan',
        self::STATUS_SELESAI => 'Selesai',
    ];

    // Status yang belum final (dana sudah diajukan/dicairkan tapi belum ada bukti lengkap)
    const STATUSES_BERJALAN = [self::STATUS_DIAJUKAN, self::STATUS_BERJALAN];

    protected $fillable = [
        'fk_id_standar',
        'fk_id_program_kegiatan',
        'fk_id_pagu_tahun_anggaran',
        'fk_id_realisasi_bulan',
        'tanggal_realisasi',
        'nominal',
        'status',
        'keterangan',
        'dokumen',
        'google_drive_file_id',
        'google_drive_url',
        'google_drive_path',
    ];

    protected $casts = [
        'tanggal_realisasi' => 'date',
    ];

    public function standar()
    {
        // Setiap realisasi nominal berhubungan ke 1 Standar
        // FK: fk_id_standar → PK: id (table Standar)
        return $this->belongsTo(Standar::class, 'fk_id_standar');
    }

    public function programKegiatan()
    {
        // Setiap realisasi nominal terhubung ke 1 Program Kegiatan
        // FK: fk_id_program_kegiatan → PK: id (table ProgramKegiatan)
        return $this->belongsTo(ProgramKegiatan::class, 'fk_id_program_kegiatan');
    }

    public function paguTahunAnggaran()
    {
        // Setiap realisasi nominal terkait 1 Pagu Tahun Anggaran
        // FK: fk_id_pagu_tahun_anggaran → PK: id (table PaguTahunAnggaran)
        return $this->belongsTo(PaguTahunAnggaran::class, 'fk_id_pagu_tahun_anggaran');
    }

    public function bulan()
    {
        // Setiap realisasi nominal dicatat untuk 1 bulan tertentu
        // FK: fk_id_realisasi_bulan → PK: id (table RealisasiBulan)
        return $this->belongsTo(RealisasiBulan::class, 'fk_id_realisasi_bulan');
    }
}
