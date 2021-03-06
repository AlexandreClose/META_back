<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class analysis extends Model
{
    public function representation() 
    {
        return $this->belongsTo('App\representation_type', 'representation_type', 'name');
    }   

    public function theme()
    {
        return $this->belongsTo('App\theme', 'theme_name', 'name');
    }

    public function owner()
    {
        return $this->belongsTo('App\user', 'owner_id', 'uuid');
    }

    public function analysis_columns()
    {
        return $this->hasMany('App\analysis_column', 'analysis_id', 'id');
    }
}
