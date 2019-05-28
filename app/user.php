<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class user extends Model
{
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    private $rules = array(
        'role' => 'required|min:3',
        'firstname'  => 'required|min:3',
        'lastname' => 'required|min:3',
        'service' => 'required|min:3',
        'direction' => 'required|min:3',
        'mail' =>'required|email',
        'phone'=>'min:10|max:10'
    );

    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, $this->rules);
        // return the result
        if( $v->fails()){
            error_log(implode($v->errors()->all()));
            return false;
        }
        return true;
    }
}

