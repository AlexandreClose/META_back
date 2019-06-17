<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tag extends Model
{
    public function datasets(){
        return $this->belongsToMany('App\dataset', 'dataset_has_tag', 'name', 'id');
    }
}
