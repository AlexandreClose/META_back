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


//Index routes : Elasticsearch
Route::get('/index/{quantity?}','IndexController@getAllIndex');
Route::get('/index/get/{name}/{quantity?}','IndexController@getIndexByName');
Route::get('/index/data/{name}/{id}','IndexController@getIndexDataByNameAndId');

//Datasets routes : Mysql
Route::get('/datasets/data/validate','DatasetController@getDatasetsToValidate');
Route::get('/datasets/{quantity?}','DatasetController@getAllDatasets');
Route::post('/datasets/update',"DatasetController@addOrUpdateDataset");

//Users routes : Mysql
Route::get('/user/{quantity?}','UserController@getAllUsers');

//Datatypes routes : Mysql
Route::get('/datatypes/{quantity?}','DataTypesController@getAllDataTypes');

//Representation types routes : Mysql
Route::get('/representationTypes/{quantity?}','RepresentationTypesController@getAllRepresentationTypes');

//Columns routes : Mysql
Route::post('/column/update','ColumnController@createColumn');

//Routes de test
Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/','UserController@updateUserWithData');
