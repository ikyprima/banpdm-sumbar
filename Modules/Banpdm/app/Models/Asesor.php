<?php

namespace Modules\Banpdm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Banpdm\Database\Factories\AsesorFactory;

class Asesor extends Model
{
    use HasFactory;
    protected $table = "tb_asesor";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nomor_induk',
        'nama',
        'email',
        'no_hp',
        'alamat',
        'latitude',
        'longitude',
        'instansi',
        'foto',
        'id_wilayah',
    ];
        public function wilayah(){
        return $this->belongsTo(Wilayah::class, 'id_wilayah', 'kode');
    }
    // protected static function newFactory(): AsesorFactory
    // {
    //     // return AsesorFactory::new();
    // }
}
