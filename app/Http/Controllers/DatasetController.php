<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dataset;
use Illuminate\Support\Carbon;
use App\representation_type;
use App\dataset_has_representation;
use App\theme;
use App\user;
use App\column;

class DatasetController extends Controller
{
    function getAllDatasets(Request $request, $quantity = null, $offset = null){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'),false);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    function UpdateDataset(Request $request){
        $postbody='';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        }
        else{
            abort(400);
        }

        $dataset = null;
        if(isset($postbody['id'])){
            $dataset = dataset::where('uuid', '=', $postbody['uuid'])->first();
        }
        if($dataset == null){
            abort(400);
        }


        if(!$dataset->validate($postbody)){
            abort(400);
        }
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
        $visibility = $request->get('visibility');

        $dataset->name = $name;
        $dataset->validated = true;
        $dataset->description = $description;
        $dataset->creator = $creator;
        $dataset->contributor = $contributor;
        $dataset->created_date = $date;
        $dataset->updated_date = Carbon::now();
        $dataset->visibility= $visibility;
        $dataset->user = $creator;
        $dataset->producer = $creator;
        $dataset->themeName = $metier;
        $theme = theme::where('name', $metier)->first();
        if($theme == null){
            abort(400);
        }

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
            $file = $request->file('uploadFile');
            $file->move(storage_path().'/uploads',$name.'.'.$file->getClientOriginalExtension());

    }

    public function getDatasetsToValidate(Request $request){
        $data = DatasetController::getAllAccessibleDatasets($request,$request->get('user'),true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllAccessibleDatasets(Request $request,user $user = null, bool $validate = false){
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
            else{
                $datasets = dataset::where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->get();
            }
            break;

            case "Référent-Métier":
            if($validate){
                $datasets = dataset::where([['validated','=',false],['conf_ready','=',true],['upload_ready',"=",true]])->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('visibility',['job_referent','worker','all'])->whereIn('themeName',$themes)->orderBy("created_date","desc")->get();
            }
            else{

            $datasets = dataset::whereIn('visibility',['job_referent','worker','all'])->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('themeName',$themes)->get();
            $datasets = $datasets->merge($directdatasets);
            $columns = column::whereIn('visibility',['job_referent','worker','all'])->whereIn('themeName',$themes)->get();
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
            }
            else{
            $datasets = dataset::whereIn('visibility',['worker','all'])->where([['validated','=',true],['conf_ready','=',true],['upload_ready',"=",true]])->whereIn('themeName',$themes)->get();
            $datasets = $datasets->merge($directdatasets);
            $columns = column::whereIn('visibility',['worker','all'])->whereIn('themeName',$themes)->get();
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


}
