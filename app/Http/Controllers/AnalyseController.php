<?php

namespace App\Http\Controllers;

use App\dataset;
use Illuminate\Http\Request;
use App\analysis;
use App\representation_type;
use App\theme;
use App\analysis_column;
use App\Http\DatasetController;


class AnalyseController extends Controller
{
    public function saveAnalyse(Request $request) {
        $user = $request->get('user');
        $analyse = new analysis();
        $analyse->name = $request->get('name');
        $representation = representation_type::where('name', $request->get('representation_type'))->first();
        if($representation == null){
            error_log("missing representation");
            abort(400, "bad representation");
        }
        $analyse->representation_type = $request->get('representation_type');
        $analyse->shared = $request->get('shared');
        $analyse->visibility = $request->get('visibility') != null ? $request->get('visibility') : 'all';
        $analyse->isStats = $request->get('isStats');
        $analyse->owner_id = $user->uuid;
        $analyse->description = $request->get('description');
        $analyse->body = json_encode($request->get('body'));
        $analyse->usage = $request->get('usage');
        $theme_name = theme::where('name', $request->get('theme_name'))->first();
        if($theme_name == null){
            error_log("missing theme");
            abort(400, "missing theme or theme don't exist");
        }
        $analyse->theme_name = $request->get('theme_name');

        $analyse->save();
        
        $analyse = analysis::where('name', $request->get('name'))->first();

        AnalyseController::createAnalysisColumn($request, $analyse->id);

        return response($analyse)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public static function createAnalysisColumn($request, $id){
        $user = $request->get('user');
        $analyse = analysis::where('id', $id);
        $analysis_columns = [];
        $analysis_columns = $request->get('analysis_column');
        error_log('FOR');
        for($i = 0; $i < count($analysis_columns); $i++){
            $analysis_column = new analysis_column();
            $analysis_column->field = $analysis_columns[$i]['field'];
            $analysis_column->analysis_id = $id;
            $analysis_column->databaseName = $analysis_columns[$i]['databaseName'];
            error_log($analysis_columns[$i]['databaseName']);
            $analysis_column->color_code = $analysis_columns[$i]['color_code'] == null ? '' : $analysis_columns[$i]['color_code'];
            error_log($analysis_columns[$i]['usage']);
            $analysis_column->usage = $analysis_columns[$i]['usage'];
            try {
                $analysis_column->save();
            } catch(Exception $e) {
                error_log('error');
            }
        }
    }

    public function getAnalysisById(Request $request, $id){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;

        $analysis = analysis::with('fields')->where('id', $id)->first();
        foreach($analysis->fields as $field){
            if(array_search(dataset::where('databaseName', $field->databaseName), $datasets) == null){
                abort(403);
            }
        }
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function deleteAnalysis(Request $request, $id){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;

        $analysis = analysis::where('id', $id)->first();
        if(($analysis->owner_id != $user->uuid && $user->role != "Administrateur")|| $analyse != null){
            abort(403);
        }
        $analysis->delete();
    }

    public function getAllAccessibleAnalysis(Request $request, $shared = false){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $analysis = $user->analysis();

        foreach($analysis as $key=>$analyse){
            foreach($analysis->columns as $column){
                if(!$shared && (!$analyse->shared || !array_search(dataset::where('id', $column->dataset_id), $datasets))){
                    unset($analysis[$key]);
                } else if($shared && !$analyse->shared && !array_search(dataset::where('id', $column->dataset_id), $datasets)){
                    unset($analysis[$key]);
                }
            }
        }

        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllAnalysis(Request $request){
        $analysis = Analysis::with('analysis_columns')->get();
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllSavedAnalysis(Request $request){
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $analysis = $user->saved_analysis();
        foreach($analysis as $key=>$analyse){
            foreach($analysis->columns as $column){
                if(($user->uuid != $analyse->owner_id && !$analysis->shared) || !array_search(dataset::where('id', $column->dataset_id), $datasets)){
                    unset($analysis[$key]);
                }
            }
        }
        
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
