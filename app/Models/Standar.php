<?php

namespace App\Models;

use App\Models\User;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Standar extends Model
{
    use HasRoles, HasUuids;

    protected $connection = 'mysql';
    protected $table = 'tb_standar'; // ✅ beri tahu Laravel nama tabel aslinya

    public $incrementing = false;
    protected $keyType = 'string';


    protected $fillable = [
        'nama_standar',
        'kode',
    ];



    // keterangan relasi users
    //  User::class → model yang berhubungan
    // 'tb_standar_user' → nama tabel pivot yang kamu tentukan manual di sini
    // 'standar_id' → nama kolom pivot yang menunjuk ke Standar
    // 'user_id' → nama kolom pivot yang menunjuk ke User

    public function users()
    {
        return $this->belongsToMany(User::class, 'tb_standar_user', 'standar_id', 'user_id');
    }


    public function programkegiatan()
    {
        return $this->hasMany(ProgramKegiatan::class, 'id_standar', 'id');
        // Local key = id di tb_standar
        // Foreign key = id_standar di tb_program_kegiatan
    }
}
