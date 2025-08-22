<?php

namespace Modules\Banpdm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Banpdm\Database\Factories\PenugasanFactory;

class Penugasan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = "tb_penugasan";
    protected $fillable = ['asesor_id','tanggal_penugasan','tanggal_penugasan_selesai','keterangan','status','latitude_awal','longitude_akhir','tahun','jarak','waktu'];

    // protected static function newFactory(): PenugasanFactory
    // {
    //     // return PenugasanFactory::new();
    // }

    public function detail(){
        return $this->hasMany(PenugasanDetail::class, 'penugasan_id', 'id');
    }
    public function asesor(){
        return $this->belongsTo(Asesor::class, 'asesor_id', 'id');
    }
}
