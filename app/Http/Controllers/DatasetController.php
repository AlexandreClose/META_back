<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dataset;
use Illuminate\Support\Carbon;
use App\representation_type;
use App\dataset_has_representation;
use App\theme;
use App\user;
use App\auth_users;
use App\column;
use App\dataset_has_tag;
use App\tag;
use App\user_saved_dataset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\json_decode;

class DatasetController extends Controller
{
    function getAllDatasets(Request $request, $quantity = null, $offset = null){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'),false);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function updateDataset(Request $request){
        $role = $request->get('user')->role;
        if($role != "Référent-Métier" && $role != "Administrateur"){
            abort(403);
        }
        /*
        $postbody='';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        }
        else{
            error_log("")
            error_log("no body in request");
            abort(400);
        }*/

        $dataset = null;
        if($request->get('id') != null){
            $dataset = dataset::where('id', '=', $request->get('id'))->first();
        }
        if($dataset == null){
            error_log("no dataset with that id");
            abort(400);
        }

        /*
        if(!$dataset->validate($postbody)){
            error_log("not validated dataset format");
            abort(400);
        }*/
        $name = $request->get('name');
        $description = $request->get('description');
        $tags = $request->get('tag');
        $producer = $request->get('producer');
        $license = $request->get('license');
        $created_date = $request->get('created_date');
        $creator = $request->get('creator');
        $contributor = $request->get('contributor');
        $frequency = $request->get('frequency');

        $visualisations = $request->get('visualisations');
        $visualisations = json_decode($visualisations);
        $visibility = $request->get('visibility');
        $theme = $request->get('theme');
        $users = $request->get('users');
        $JSON = (bool)$request->get('JSON');
        $GEOJSON = (bool)$request->get('GEOJSON');

        $dataset->name = $name;
        $dataset->description = $description;
        $tags = json_decode($tags);
        foreach($tags as $tag){
            $_tag = tag::where('name', $tag)->first();
            if($_tag == null){
                error_log("Créer un nouveau tag");
                $_tag = new tag();
                $_tag->name = $tag;
                $_tag->save();
            }
            error_log("Créer la relation entre ".$dataset->name." et ".$_tag->name);
            if((dataset_has_tag::where('id', $dataset->id)->where('name', $_tag->name)->first() == null)){
                $dataset_tag = new dataset_has_tag();
                $dataset_tag->id = $dataset->id;
                $dataset_tag->name = $_tag->name;
                $dataset_tag->save();
            }
        }
        error_log("first foreach passed");
        $dataset->producer = $producer;
        $dataset->license = $license;
        $dataset->created_date = $created_date;
        $dataset->creator = $creator;
        $dataset->contributor = $contributor;
        $dataset->update_frequency = $frequency;

        foreach($visualisations as $visualisation){
            $type = representation_type::where('name', $visualisation)->first();
            if((dataset_has_representation::where('representationName', $type->name)->where('datasetId', $dataset->id)->first()) == null){
                $types = new dataset_has_representation();
                $types->datasetId = $dataset->id;
                $types->representationName = $type->name;
                $types->save();
            }
        }
        error_log("second foreach passed");
        $dataset->visibility= $visibility;
        $theme_from_base = theme::where('name', $theme)->first();
        if($theme_from_base == null){
            error_log($theme_from_base);
            abort(400);
        }
        $users = json_decode($users);
        foreach($users as $user_id){
            $auth_user = user::where('uuid',$user_id)->first();
            if($auth_user == null || ((auth_users::where('uuid', $auth_user->uuid)->where('id', $dataset->id)->first()) != null)            ){
                continue;
            }
            $auth_users = new auth_users();
            $auth_users->id = $dataset->id;
            $auth_users->uuid = $auth_user->uuid;
            $auth_users->save();
        }
        error_log("last foreach passed");
        $dataset->GEOJSON = $GEOJSON;
        $dataset->JSON = $JSON;

        $dataset->validated = true;

        $dataset->save();

    }

    public function uploadDataset(Request $request){
            error_log($request);
            $description = $request->get('description');
            $name = $request->get('name');
            $tags = $request->get('tag');
            $metier = $request->get('metier');
            $JSON = $request->get('JSON');
            $GEOJSON = $request->get('GEOJSON');
            $visualisations = $request->get('visualisations');
            $visualisations = json_decode($visualisations);
            $date = $request->get('date');
            $creator = $request->get('creator');
            $contributor = $request->get('contributor');
            $dataset = new dataset();
            $dataset->name = $name;
            $dataset->JSON = (bool)$JSON;
            $dataset->GEOJSON = (bool)$GEOJSON;
            $dataset->validated = false;
            $dataset->description = $description;
            $dataset->creator = $creator;
            $dataset->contributor = $contributor;
            $dataset->license = "Fermée";
            $dataset->created_date = $date;
            $dataset->updated_date = Carbon::now();
            $dataset->realtime = false;
            $dataset->conf_ready = false;
            $dataset->upload_ready = false;
            $dataset->open_data = false;
            $dataset->visibility= "job_referent";
            $dataset->user = $creator;
            $dataset->producer = $creator;
            $dataset->themeName = $metier;
            $dataset->databaseName = str_replace("-","_",Str::slug($name));
            $file = $request->file('uploadFile');
            error_log(dd($file));
            $file->move(storage_path().'/uploads',$dataset->databaseName.'.'.$file->getClientOriginalExtension());
            $theme = theme::where('name',$metier)->first();

            if($theme == null){
                error_log($theme);
                error_log($metier);
                abort(400);
            }
            $dataset->save();
            $dataset = dataset::where('name',$name)->first();
            foreach($visualisations as $visualisation){
                $type = representation_type::where('name',$visualisation)->first();
                $types = new dataset_has_representation();
                $types->datasetId = $dataset->id;
                $types->representationName = $type->name;
                $types->save();
            }

    }

    public function getDatasetsToValidate(Request $request){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'),true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public static function getAllAccessibleDatasets(Request $request,user $user = null, bool $validate = false, bool $saved = false, bool $favorite = false){
        if($user == null){
            $user = $request->get('user');
        }
        $themes = $user->themes;
        $role = $user->role;
        $directdatasets = $user->datasets;
        $directcolumns = $user->columns;
        switch($role){
            case "Administrateur":
                if($validate){
                    $datasets = dataset::where([['validated','=',false],['conf_ready','=',true],['upload_ready',"=",true]])->orderBy("created_date","desc")->get();
                }
                elseif ($saved) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', false)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                }
                elseif ($favorite) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', true)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                }
                else{
                    $datasets = dataset::where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->get();
                }
                break;
            case "Référent-Métier":
                if($validate){
                    $datasets = dataset::where([['validated','=',false],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('visibility',['job_referent','worker'])->whereIn('themeName',$themes)->orderBy("created_date","desc")->get();
                    $datasets = $datasets->merge(dataset::where([['validated','=',false],['conf_ready','=',true],['upload_ready',"=",true]])->where('visibility','all')->orderBy("created_date","desc")->get());
                }
                elseif ($saved) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', false)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->whereIn('datasets.visibility',['job_referent','worker'])
                        ->whereIn('datasets.themeName',$themes)
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                    $datasets = $datasets->merge(DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', false)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility', 'all')
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get());
                }
                elseif ($favorite) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', true)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->whereIn('datasets.visibility',['job_referent','worker'])
                        ->whereIn('datasets.themeName',$themes)
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                    $datasets = $datasets->merge(DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', true)
                        ->where([['datasets.validated','=',true],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility', 'all')
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get());
                }
                else{
                    $datasets = dataset::whereIn('visibility',['job_referent','worker'])->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('themeName',$themes)->get();
                    $datasets = $datasets->merge(dataset::where('visibility', 'all')->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->get());
                    $datasets = $datasets->merge($directdatasets);
                    $columns = column::whereIn('visibility',['job_referent','worker'])->whereIn('themeName',$themes)->get();
                    $columns = $columns->merge(column::whereIn('visibility', ['all', null])->get());
                    $columns = $columns->merge($directcolumns);
                    $array= [];
                    foreach($columns as $column){
                        array_push($array,$column->dataset);
                    }
                    $datasets->merge($array);
                }
                break;
            case "Utilisateur":
                if($validate){
                    $datasets = [];
                    return $datasets;
                }
                elseif ($saved) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', false)
                        ->where([['datasets.validated','=',false],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility', 'worker')
                        ->whereIn('datasets.themeName',$themes)
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                    $datasets = $datasets->merge(DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', false)
                        ->where([['datasets.validated','=',false],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility', 'all')
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get());
                }
                elseif ($favorite) {
                    $datasets = DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', true)
                        ->where([['datasets.validated','=',false],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility','worker')
                        ->whereIn('datasets.themeName',$themes)
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get();
                    $datasets = $datasets->merge(DB::table('datasets')
                        ->join('user_saved_datasets', 'datasets.id', '=', 'user_saved_datasets.id')
                        ->where('user_saved_datasets.uuid', $user->uuid)
                        ->where('user_saved_datasets.favorite', true)
                        ->where([['datasets.validated','=',false],['datasets.conf_ready','=',true],['datasets.upload_ready',"=",true]])
                        ->where('datasets.visibility', 'all')
                        ->select('datasets.*')
                        ->orderBy("created_date","desc")
                        ->get());
                }
                else{
                    $datasets = dataset::whereIn('visibility', 'worker')->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('themeName',$themes)->get();
                    $datasets = $datasets->merge(dataset::where('visibility', 'all')->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->get());
                    $datasets = $datasets->merge($directdatasets);
                    $columns = column::whereIn('visibility','worker')->whereIn('themeName',$themes)->get();
                    $columns = $columns->merge(column::whereIn('visibility', ['all', null])->get());
                    $columns = $columns->merge($directcolumns);
                    $array= [];
                    foreach($columns as $column){
                        array_push($array,$column->dataset);
                    }
                    $datasets->merge($array);
                }
                break;
            default:
                $datasets = [];
                return $datasets;
            }
        return $datasets;

    }

    public function getRepresentationsOfDataset($id){
        $dataset = dataset::where('id',$id)->first();
        if($dataset == null){
            abort(404);
        }
        $representations = $dataset->representations;
        return response($representations)->header('Content-Type', 'application/json')->header('charset', 'utf-8');

    }

    public function getAllColumnFromDataset(Request $request,$id){
        $dataset = dataset::where('id', $id)->first();
        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, $dataset);
        return response($columns)->header('Content-Type', 'application/json')->header('charset', 'utf-8');

    }

    public static function getAllAccessibleColumnsFromADataset(Request $request, dataset $dataset){
        $user = $request->get('user');
        $role = $user->roles->role;
        $themes = $user->theme;
        switch($role){
            case "Administrateur":
                $columns = column::where('dataset_id', $dataset->id)->get();
                break;
            case "Référent-Métier":
                $columns = column::where('dataset_id', $dataset->id)->whereIn('visibility',['job_referent','worker'])->whereIn('themeName',$themes)->get();
                $columns->merge(column::where('dataset_id', $dataset->id)->whereIn('visibility', ['all', null]));
                break;
            case "Utilisateur":
                $columns = column::where('dataset_id', $dataset->id)->where('visibility', 'worker')->where('themeName',$themes)->get();
                $columns->merge(column::where('dataset_id', $dataset->id)->whereIn('visibility', ['all', null]));
                break;
            default:
                $columns = [];
                break;
            }
        $array = [];
        $directcolumns = $user->columns;
        foreach($directcolumns as $dc){
            if($dc->dataset_id == $dataset->id){
                array_push($array, $dc);
            }
        }
        $columns = $columns->merge($array);
        return $columns;
    }


    public static function saveAndFavoriteDataset(user $user, dataset $dataset, $favorite = false){
        $saved_ds =  user_saved_dataset::where('uuid', $user->uuid)->where('id', $dataset->id)->first();
        if($saved_ds == null) {
            $saved_ds = new user_saved_dataset();
            $saved_ds->id = $dataset->id;
            $saved_ds->uuid = $user->uuid;   
        }
        $saved_ds->favorite = $favorite;
        $saved_ds->save(); 
    }

    public function saveDataset(Request $request, $id) {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset);
    }

    public function favoriteDataset(Request $request, $id){
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset, true);
    }

    public function unsaveDataset(Request $request, $id){
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        $saved_ds =  user_saved_dataset::where('uuid', $user->uuid)->where('id', $dataset->id)->first();
        if($saved_ds != null){
            $saved_ds->delete();
        }
    }

    public function getAllAccessibleSavedDatasets(Request $request){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'), false, true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllAccessibleFavoriteDatasets(Request $request){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'), false, false, true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
