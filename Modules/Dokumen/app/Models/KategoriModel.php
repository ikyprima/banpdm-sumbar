<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Dokumen\Database\Factories\KategoriModelFactory;

class KategoriModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'categories';
    protected $fillable = ['name'];

    // protected static function newFactory(): KategoriModelFactory
    // {
    //     // return KategoriModelFactory::new();
    // }
}
