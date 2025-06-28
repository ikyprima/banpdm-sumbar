<?php

namespace Modules\Dokumen\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Dokumen\Database\Factories\FileModelFactory;
use Str;
class FileModel extends Model
{
     protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'file_uploads';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['title_file','description','name','original_name','filepath','extension','mime_type','size','user_id','categories_id','is_public','downloads'];

    function kategori(){
        return $this->hasOne(KategoriModel::class,'id','categories_id');
    }
    // protected static function newFactory(): FileModelFactory
    // {
    //     // return FileModelFactory::new();
    // }
}
