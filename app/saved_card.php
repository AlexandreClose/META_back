<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class saved_card extends Model
{
    
    public function user()
    {
        return $this->hasOne('App\user', 'user_uuid', 'uuid');
    }

    public function analysis()
    {
        return $this->hasOne('App\analysis', 'analysis_id', 'id');
    }
}
