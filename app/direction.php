<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class direction extends Model
{
    protected $primaryKey = 'direction';
    public $incrementing = false;

    public function users(){
        $this->hasMany('App\user', 'direction', 'direction');
    }
}
