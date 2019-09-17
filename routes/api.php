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

use App\Http\Controllers\AnalyseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response("", 200);
});


//Index routes : Elasticsearch
Route::get('/index/{quantity?}', 'IndexController@getAllIndex'); # doc OK
Route::get('/index/date/{name}', 'IndexController@getAllDateFieldsFromAnIndexFromItsName'); # doc OK
Route::get('/index/fields/{name}', 'IndexController@getAllFieldsFromIndexByName'); # doc OK
Route::get('/index/accessiblefields/{name}', 'IndexController@getAllAccessibleFieldsFromIndexByName'); # doc OK

Route::get('/index/get/{name}/{quantity?}/{offset?}/{date_col?}/{start_date?}/{end_date?}', 'IndexController@getIndexByName');
//Route::get('/index/file/{name}', 'IndexController@getIndexFile');
//Route::post('/index/geo', 'IndexController@getIndexFromCoordinatesInShape');
Route::post('/liteIndex', 'IndexController@getLiteIndex'); # doc OK

//Index routes: Elasticsearch and InfluxDB
Route::post('/index/join', 'IndexController@join'); # doc OK
Route::post('/index/fromPolygon', 'IndexController@getInPointInPolygon'); # doc OK

//Index routes: InfluxDB
Route::post('/index/last', 'IndexController@getLast'); # doc OK


//Analyse routes : Mysql
Route::post('/analyse/save', 'AnalyseController@saveAnalyse');
Route::get('/analyse/get/{id}', 'AnalyseController@getAnalysisById'); #todo review
Route::get('/analyse/all', 'AnalyseController@getAllAccessibleAnalysis'); # doc OK
Route::get('/analyse/saved', 'AnalyseController@getAllSavedAnalysis'); # doc OK
Route::delete('/analyse/{id}', 'AnalyseController@deleteAnalysis');


//Datasets routes : Mysql
Route::get('/datasets/data/validate', 'DatasetController@getDatasetsToValidate'); # doc Ok
Route::get('/datasets/all/', 'DatasetController@getAllDatasets'); # doc Ok
Route::get('/datasets/representations/{id}', 'DatasetController@getRepresentationsOfDataset'); # doc Ok
Route::get('/dataset/{id}', "DatasetController@getDatasetById"); # doc Ok
Route::post('/datasets/update', "DatasetController@updateDataset");
Route::post('/datasets/upload', 'DatasetController@uploadDataset');
Route::get('/dataset/{id}/columns', "DatasetController@getAllColumnFromDataset"); # doc Ok
Route::get('/dataset/{id}/save', "DatasetController@saveDataset"); # doc Ok
Route::get('/dataset/{id}/favorite', "DatasetController@favoriteDataset"); # doc Ok
Route::get('/dataset/{id}/unsave', "DatasetController@unsaveDataset"); # doc Ok
Route::get('/dataset/{id}/unfavorite', "DatasetController@unsaveDataset"); # doc Ok
Route::get('/datasets/favorite/', "DatasetController@getAllAccessibleFavoriteDatasets"); # doc Ok
Route::get('/datasets/filters', 'DatasetController@getFilterDatasets');
Route::get('/datasets/saved/', "DatasetController@getAllAccessibleSavedDatasets"); # doc Ok
Route::get('/datasets/size/{type?}', "DatasetController@getDatasetsSize"); # doc OK

//Users routes : Mysql
Route::get('/user', 'UserController@getAllUsers'); # doc Ok
Route::get('self', 'UserController@getConnectedUserData'); # doc OK
Route::get('/users/name/{quantity?}', 'UserController@getUsersName'); # doc Ok
Route::post('/user/create', 'UserController@addUser');
Route::post('/user/theme', 'UserController@addUserTheme');
Route::delete('/user/theme', 'UserController@deleteUserTheme');
Route::get('/user/block/{uuid}', 'UserController@blockUser'); # doc Ok
Route::get('/user/unblock/{uuid}', 'UserController@unblockUser'); # doc Ok
Route::get('/user/color', 'UserController@getAllUserColor');
Route::post('user/color', 'UserController@addColorToUser');
Route::post('color/update', 'UserController@updateColorUser');
Route::delete('user/color', 'UserController@removeColorFromUser');

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
Route::delete('/theme/{name}/{newName}', 'ThemeController@deleteTheme');
Route::put('/theme', 'ThemeController@updateTheme');

//Roles routes : Mysql
Route::get('/role', 'RolesController@getAllRoles');

//Tags routes : Mysql
Route::get('/tag/{quantity?}', 'TagsController@getAllTags');

//Directions routes : Mysql
Route::post('/direction', 'DirectionController@addDirection');
Route::delete('/direction/{name}', 'DirectionController@delDirection');
Route::put('/direction', 'DirectionController@updateDirection');
Route::get('/direction', 'DirectionController@getAllDirections');

//Services routes : Mysql
Route::post('/service', 'ServiceController@addService');
Route::delete('/service/{name}', 'ServiceController@delService');
Route::put('/service', 'ServiceController@updateService');
Route::get('/service', 'ServiceController@getAllServices');

//Saved cards routes : Mysql
Route::get('/saved_cards', 'SavedCardsController@getAllSavedCards');
Route::post('/saved_card', 'SavedCardsController@saveCard');
Route::put('/saved_card', 'SavedCardsController@updateCard');
Route::delete('/save_card', 'SavedCardsController@deleteCard');

//Routes de test
//Route::get('/user/add/{uuid}','UserController@createUserIfDontExist');
Route::post('/user/update/', 'UserController@updateUserWithData');
