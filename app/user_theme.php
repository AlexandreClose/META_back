<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * @property mixed uuid
 * @property mixed name
 */
class user_theme extends Model
{
    public $table = "user_theme";
    protected $primaryKey = ['uuid','name'];
    public $incrementing = false;
    protected $keyType = 'string';

    private $rules = array(
        'uuid' => 'required|min:3',
        'name' => 'required|min:3|alpha',
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
}
