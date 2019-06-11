<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dataset;
use Illuminate\Support\Carbon;
use App\representation_type;
use App\dataset_has_representation;
use App\theme;

class DatasetController extends Controller
{
    function getAllDatasets($quantity = null){
        $data = [];
        if(isset($quantity)){
            $datasets  = dataset::whereIn('themeName', $user->themes())->take($quantity)->get();
        }else{
            $datasets  = dataset::all();
        }

        foreach($datasets as $dataset){
            $dataset = json_decode($dataset);
            array_push($data,$dataset);
        }
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

    public function getDatasetsToValidate(){
        $data = [];
        $datasets = dataset::where('validated',false)->where('conf_ready',true)->where('upload_ready',true)->orderBy("created_date","desc")->take(5)->get();
        foreach($datasets as $dataset){
            $dataset = json_decode($dataset);
            array_push($data,$dataset);
        }
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
