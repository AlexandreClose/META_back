<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class analysis extends Model
{
    public function representation() 
    {
        return $this->belongsTo('App\representation_type', 'representation_type', 'name');
    }   

    public function themes()
    {
        return $this->belongsToMany('App\theme', 'analysis_theme', 'id', 'name');
    }

    public function owner()
    {
        return $this->belongsTo('App\user', 'owner_id', 'uuid');
    }

    public function columns()
    {
        return $this->belongsToMany('App\column', 'analysis_column', 'id', 'name');
    }
}
