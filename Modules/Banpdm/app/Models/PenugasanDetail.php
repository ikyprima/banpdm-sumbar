<?php

namespace Modules\Banpdm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Banpdm\Database\Factories\PenugasanDetailFactory;

class PenugasanDetail extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'tb_penugasan_detail';
    protected $fillable = ['penugasan_id','sekolah_id','status','keterangan','tanggal_check_in','latitude_check_in','longitude_check_in'];

    // protected static function newFactory(): PenugasanDetailFactory
    // {
    //     // return PenugasanDetailFactory::new();
    // }
    public function penugasan(){
        return $this->belongsTo(Penugasan::class, 'penugasan_id', 'id');
    }
    public function sekolah(){
        return $this->belongsTo(SatuanSekolah::class, 'sekolah_id', 'id');
    }
}
