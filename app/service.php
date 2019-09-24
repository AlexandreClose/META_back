<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class service extends Model
{
    protected $primaryKey = 'service';
    public $incrementing = false;

    public function users(){
        $this->hasMany('App\user', 'service', 'service');
    }
}
