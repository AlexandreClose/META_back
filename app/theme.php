<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class theme extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    public function analysis()
    {
        return $this->belongsToMany('App\analysis', 'analysis_theme', 'name', 'id');
    }
}
