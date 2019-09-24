<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed themeName
 * @property mixed dataset_id
 * @property mixed data_type_name
 * @property mixed visibility
 * @property mixed main
 * @property mixed name
 */
class column extends Model
{
    public function dataset(){
        return $this->belongsTo('App\dataset','id','dataset_id');
    }
}
