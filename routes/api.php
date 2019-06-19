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


//Datasets routes : Mysql
Route::get('/datasets/data/validate','DatasetController@getDatasetsToValidate');
Route::get('/datasets/all/{quantity?}/{offset?}','DatasetController@getAllDatasets');
Route::get('/datasets/representations/{id}','DatasetController@getRepresentationsOfDataset');
Route::post('/datasets/update',"DatasetController@updateDataset");
Route::post('/datasets/upload','DatasetController@uploadDataset');


//Users routes : Mysql
Route::get('/user/{quantity?}','UserController@getAllUsers');
Route::get('/self','UserController@getConnectedUserData');
Route::get('/users/name/{quantity?}','UserController@getUsersName');

//Datatypes routes : Mysql
Route::get('/datatypes/{quantity?}','DataTypesController@getAllDataTypes');

//Representation types routes : Mysql
Route::get('/representationTypes/{quantity?}','RepresentationTypesController@getAllRepresentationTypes');

//Columns routes : Mysql
Route::post('/column/update','ColumnController@createColumn');

//themes routes : Mysql
Route::get('/theme/{quantity?}','ThemeController@getAllThemes');

//Roles routes : Mysql
Route::get('/role/{quantity?}','RolesController@getAllRoles');

//Tags routes : Mysql
Route::get('/tag/{quantity?}', 'TagsController@getAllTags');

//Routes de test
//Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/','UserController@updateUserWithData');
