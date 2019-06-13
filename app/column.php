<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class column extends Model
{
    public function dataset(){
        return $this->belongsTo('App\dataset','id','dataset_id');
    }
}
