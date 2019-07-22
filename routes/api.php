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
Route::get('/index/date/{name}', 'IndexController@getAllDateFieldsFromAnIndexFromItsName');
Route::get('/index/fields/{name}', 'IndexController@getAllFieldFromIndexByName');
Route::get('/index/get/{name}/{quantity?}/{offset?}/{date_col?}/{start_date?}/{end_date?}','IndexController@getIndexByName');
Route::get('/index/file/{name}', 'IndexController@getIndexFile');
Route::post('/index/geo', 'IndexController@getIndexFromCoordinatesInShape');

//Analyse routes : Mysql
Route::get('/analyse/save', 'Analysecontroller@saveAnalyse');
Route::get('/analyse/get/{id}', 'AnalyseController@getAnalysisFromId');
Route::get('/analyse/all', 'AnalyseController@getAllAccessibleAnalysis');

//Datasets routes : Mysql
Route::get('/datasets/data/validate','DatasetController@getDatasetsToValidate');
Route::get('/datasets/all/{quantity?}/{offset?}','DatasetController@getAllDatasets');
Route::get('/datasets/representations/{id}','DatasetController@getRepresentationsOfDataset');
Route::post('/datasets/update',"DatasetController@updateDataset");
Route::post('/datasets/upload','DatasetController@uploadDataset');
Route::get('/dataset/{id}/columns', "DatasetController@getAllColumnFromDataset");
Route::get('/dataset/{id}/save', "DatasetController@saveDataset");
Route::get('/dataset/{id}/favorite', "DatasetController@favoriteDataset");
Route::get('/dataset/{id}/unsave', "DatasetController@unsaveDataset");
Route::get('/dataset/{id}/unfavorite', "DatasetController@unsaveDataset");
Route::get('/datasets/favorite', "DatasetController@getAllAccessibleFavoriteDatasets");
Route::get('/datasets/saved', "DatasetController@getAllAccessibleSavedDatasets");


//Users routes : Mysql
Route::get('/user/{quantity?}','UserController@getAllUsers');
Route::get('self','UserController@getConnectedUserData');
Route::get('/users/name/{quantity?}','UserController@getUsersName');
Route::post('/user/create','UserController@addUser');

//Datatypes routes : Mysql
Route::get('/datatypes/{quantity?}','DataTypesController@getAllDataTypes');

//Representation types routes : Mysql
Route::get('/representationTypes/{quantity?}','RepresentationTypesController@getAllRepresentationTypes');

//Columns routes : Mysql
Route::post('/column/create','ColumnController@createColumn');

//themes routes : Mysql
Route::get('/theme/{quantity?}','ThemeController@getAllThemes');

//Roles routes : Mysql
Route::get('/role/{quantity?}','RolesController@getAllRoles');

//Tags routes : Mysql
Route::get('/tag/{quantity?}', 'TagsController@getAllTags');

//Directions routes : Mysql
Route::post('/direction', 'DirectionController@addDirection');
Route::delete('/direction', 'DirectionController@delDirection');
Route::get('/direction/{quantity?}', 'DirectionController@getAllDirections');

//Services routes : Mysql
Route::post('/service', 'ServiceController@addService');
Route::delete('/service', 'ServiceController@delService');
Route::get('/service/{quantity?}', 'ServiceController@getAllServices');

//Routes de test
//Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/','UserController@updateUserWithData');
