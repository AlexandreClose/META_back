<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class analysis_column extends Model
{
    /*
    protected $primaryKey = ['field', 'analysis_id', 'databaseName'];
    public $incrementing = false;
    protected $keyType = 'array';
    */

    public function analysis(){
        return $this->belongsTo('App/analysis', 'analysis_id', 'id');
    }
}
