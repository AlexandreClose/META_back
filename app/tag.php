<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tag extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    public function datasets(){
        return $this->belongsToMany('App\dataset', 'dataset_has_tags', 'name', 'id');
    }
}
