<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureSetting extends Model
{
    protected $fillable = [
        'ketua_komite_name',
        'kepala_sekolah_name',
        'signature_day',
        'signature_month',
        'signature_year',
    ];
}
