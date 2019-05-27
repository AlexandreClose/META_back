<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/',function(){
    return response("",200);
});


Route::get('/index/{quantity?}','IndexController@getAllIndex');
Route::get('/index/get/{name}/{quantity?}','IndexController@getIndexByName');
Route::get('/index/data/{name}/{id}','IndexController@getIndexDataByNameAndId');
Route::get('/index/data/validate','IndexController@getIndexToValidate');

Route::get('/user/{quantity?}','UserController@getAllUsers');

//Routes de test
Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/','UserController@updateUserWithData');
