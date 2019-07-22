<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * @property mixed name
 * @property mixed description
 */
class theme extends Model
{
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    private $rules = array(
        'name' => 'required|min:3|alpha',
        'description' => 'required|min:3',
    );

    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, $this->rules);
        // return the result
        if ($v->fails()) {
            error_log(implode($v->errors()->all()));
            return false;
        }
        return true;
    }

    public function analysis()
    {
        return $this->belongsToMany('App\analysis', 'analysis_theme', 'name', 'id');
    }
}
