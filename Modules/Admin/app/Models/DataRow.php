<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\database\Factories\DataRowFactory;

class DataRow extends Model
{
    use HasFactory;
    protected $table = 'data_rows';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): DataRowFactory
    // {
    //     // return DataRowFactory::new();
    // }
}
