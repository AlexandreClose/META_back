<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class direction extends Model
{
    protected $primaryKey = 'direction';
    public $incrementing = false;
    public $direction;
    public $description;

    public function users(){
        $this->hasMany('App\user', 'direction', 'direction');
    }
}
