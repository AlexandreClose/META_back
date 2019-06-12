<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class saved_cards extends Model
{
    
    public function user()
    {
        return $this->hasOne('App\user', 'uuid', 'uuid');
    }

    public function analysis()
    {
        return $this->hasOne('App\analysis', 'id', 'id');
    }
}
