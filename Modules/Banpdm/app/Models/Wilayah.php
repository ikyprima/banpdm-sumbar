<?php

namespace Modules\Banpdm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Banpdm\Database\Factories\WilayahFactory;

class Wilayah extends Model
{
    use HasFactory;
    protected $table="wilayah_level_1_2";
    /**
     * The attributes that are mass assignable.
     */
    // Custom primary key
    protected $primaryKey = 'kode';

    // Kalau bukan auto increment (misal kode seperti '130101')
    public $incrementing = false;

    // Kalau tipe datanya string
    protected $keyType = 'string';
    protected $fillable = ['kode','nama'];
 
    // protected static function newFactory(): WilayahFactory
    // {
    //     // return WilayahFactory::new();
    // }
    public function satuanSekolah(){
        return $this->hasMany(SatuanSekolah::class, 'kode_wilayah', 'kode');
    }
    public function asesor(){
        return $this->hasMany(Asesor::class, 'id_wilayah', 'kode');
    }
}
