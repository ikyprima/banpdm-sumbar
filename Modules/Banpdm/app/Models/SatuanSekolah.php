<?php

namespace Modules\Banpdm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Banpdm\Database\Factories\SatuanSekolahFactory;

class SatuanSekolah extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'tb_satuan_sekolah';
    protected $fillable = ['npsn','nama','jenjang_id','status','kode_wilayah','alamat','desa','kecamatan','kode_daerah','kode_pos','telepon','email','website','kepala_sekolah','latitude','longitude'];

    // protected static function newFactory(): SatuanSekolahFactory
    // {
    //     // return SatuanSekolahFactory::new();
    // }
    public function wilayah(){
        return $this->belongsTo(Wilayah::class, 'kode_wilayah', 'kode');
    }
}
