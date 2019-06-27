<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class dataset extends Model
{

    public function validate($data)
    {
        // make a new validator object
        //$v = Validator::make($data, $this->rules);
        // return the result
        //if( $v->fails()){
        //    error_log(implode($v->errors()->all()));
         //   return false;
        //}
        return true;
    }

    public function theme()
    {
        return $this->hasOne('App\theme',"name","themeName");
    }

    public function users(){
        return $this->belongsToMany('App\user','auth_users','id','uuid');
    }

    public function representations(){
        return $this->belongsToMany('App\representation_type','dataset_has_representations','datasetId','representationName');
    }

    public function tags(){
        return $this->belongsToMany('App\tag', 'dataset_has_tags', 'id', 'name');
    }

    public function columns(){
        return $this->hasMany('App\column', 'dataset_id', 'id');
    }
}
