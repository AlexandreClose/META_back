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
}
