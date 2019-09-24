<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


/**
 * @property mixed uuid
 * @property mixed role
 * @property mixed firstname
 * @property mixed lastname
 * @property mixed service
 * @property mixed direction
 * @property mixed mail
 * @property mixed phone
 * @property mixed tid
 */
class user extends Model
{
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    private $rules = array(
        'role' => 'required|min:3',
        'firstname'  => 'required|min:1',
        'lastname' => 'required|min:1',
        'service' => 'required|min:3',
        'direction' => 'required|min:3',
        'mail' =>'required|email',
        'phone'=>'digits:10|numeric',
        'tid'=>'required|min:3'
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

    public function themes()
    {
        return $this->belongsToMany('App\theme', 'user_theme', 'uuid', 'name');
    }

    public function roles()
    {
        return $this->hasOne('App\role', 'role', 'role');
    }

    public function service()
    {
        return $this->hasOne('App\service', 'service', 'service');
    }

    public function direction()
    {
        return $this->hasOne('App\direction', 'direction', 'direction');
    }

    public function analysis()
    {
        return $this->hasMany('App\analysis', 'owner_id', 'uuid');
    }

    public function datasets(){
        return $this->belongsToMany('App\dataset','auth_users','uuid','id');
    }

    public function saved_datasets(){
        return $this->belongsToMany('App\dataset','user_saved_dataset','uuid','id');
    }

    public function columns(){
        return $this->belongsToMany('App\column','colauth_users','uuid','id');
    }

    public function cards()
    {
        return $this->hasMany('App\saved_card', 'uuid', 'uuid');
    }

    public function colors()
    {
        return $this->hasMany('App\color', 'user_uuid', 'uuid');
    }

    public function saved_analysis(){
        return $this->belongsToMany('App\analysis','saved_card', 'uuid', 'uuid');
    }
}

