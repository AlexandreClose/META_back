<?php


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

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response("", 200);
});


//Index routes : Elasticsearch
Route::get('/index/{quantity?}', 'IndexController@getAllIndex');
Route::get('/index/date/{name}', 'IndexController@getAllDateFieldsFromAnIndexFromItsName');
Route::get('/index/fields/{name}', 'IndexController@getAllFieldsFromIndexByName');
Route::get('/index/accessiblefields/{name}', 'IndexController@getAllAccessibleFieldsFromIndexByName');

Route::get('/index/get/{name}/{quantity?}/{offset?}/{date_col?}/{start_date?}/{end_date?}', 'IndexController@getIndexByName');
Route::get('/index/file/{name}', 'IndexController@getIndexFile');
Route::post('/index/geo', 'IndexController@getIndexFromCoordinatesInShape');
Route::post('/liteIndex/', 'IndexController@getLiteIndex');

//Analyse routes : Mysql
Route::get('/analyse/save', 'Analysecontroller@saveAnalyse');
Route::get('/analyse/get/{id}', 'AnalyseController@getAnalysisFromId');
Route::get('/analyse/all', 'AnalyseController@getAllAccessibleAnalysis');

//Datasets routes : Mysql
Route::get('/datasets/data/validate', 'DatasetController@getDatasetsToValidate');
Route::get('/datasets/all/{quantity?}/{offset?}', 'DatasetController@getAllDatasets');
Route::get('/datasets/representations/{id}', 'DatasetController@getRepresentationsOfDataset');
Route::post('/datasets/update', "DatasetController@updateDataset");
Route::post('/datasets/upload', 'DatasetController@uploadDataset');
Route::get('/dataset/{id}/columns', "DatasetController@getAllColumnFromDataset");
Route::get('/dataset/{id}/save', "DatasetController@saveDataset");
Route::get('/dataset/{id}/favorite', "DatasetController@favoriteDataset");
Route::get('/dataset/{id}/unsave', "DatasetController@unsaveDataset");
Route::get('/dataset/{id}/unfavorite', "DatasetController@unsaveDataset");
Route::get('/datasets/favorite', "DatasetController@getAllAccessibleFavoriteDatasets");
Route::get('/datasets/saved', "DatasetController@getAllAccessibleSavedDatasets");
Route::get('/datasets/util/{tinyInt}', "DatasetController@getUtil");


//Users routes : Mysql
Route::get('/user/{quantity?}', 'UserController@getAllUsers');
Route::get('self', 'UserController@getConnectedUserData');
Route::get('/users/name/{quantity?}', 'UserController@getUsersName');
Route::post('/user/create', 'UserController@addUser');
Route::post('/user/theme', 'UserController@addUserTheme');
Route::delete('/user/theme', 'UserController@deleteUserTheme');

//Datatypes routes : Mysql
Route::get('/datatypes/{quantity?}', 'DataTypesController@getAllDataTypes');

//Representation types routes : Mysql
Route::get('/representationTypes/{quantity?}', 'RepresentationTypesController@getAllRepresentationTypes');

//Columns routes : Mysql
Route::post('/column/create', 'ColumnController@createColumn');
Route::post('/column/stats', 'ColumnController@getStats');

//themes routes : Mysql
Route::get('/theme', 'ThemeController@getAllThemes');
Route::post('/theme', 'ThemeController@addTheme');
//Route::delete('/theme', 'ThemeController@deleteTheme');
Route::put('/theme', 'ThemeController@updateTheme');

//Roles routes : Mysql
Route::get('/role', 'RolesController@getAllRoles');

//Tags routes : Mysql
Route::get('/tag/{quantity?}', 'TagsController@getAllTags');

//Directions routes : Mysql
Route::post('/direction', 'DirectionController@addDirection');
Route::delete('/direction', 'DirectionController@delDirection');
Route::put('/direction', 'ServiceDirection@updateDirection');
Route::get('/direction', 'DirectionController@getAllDirections');

//Services routes : Mysql
Route::post('/service', 'ServiceController@addService');
Route::delete('/service', 'ServiceController@delService');
Route::put('/service', 'ServiceController@updateService');
Route::get('/service', 'ServiceController@getAllServices');

//Routes de test
//Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/', 'UserController@updateUserWithData');
