<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class analysis_column extends Model
{
    protected $primaryKey = ['column_id', 'analysis_id'];
    public $incrementing = false;
    protected $keyType = 'array';

    public function analysis(){
        return $this->belongsTo('App/analysis', 'analysis_id', 'id');
    }

    public function columns(){
        return $this->belongsTo('App/column', 'column_id', 'id');
    }
}
