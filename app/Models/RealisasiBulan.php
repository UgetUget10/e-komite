<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RealisasiBulan extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'tb_realisasi_bulan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama_bulan',
    ];

    public static function fromDate(\DateTimeInterface|string $date): self
    {
        $namaBulan = \Illuminate\Support\Carbon::parse($date)->translatedFormat('F');

        return static::firstOrCreate(['nama_bulan' => $namaBulan]);
    }
}
