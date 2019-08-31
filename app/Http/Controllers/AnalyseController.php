<?php

namespace App\Http\Controllers;

use App\analysis_column;
use App\dataset;
use App\Http\Services\IndexService;
use App\saved_card;
use App\user_theme;
use Illuminate\Http\Request;
use App\analysis;
use App\representation_type;
use App\theme;


class AnalyseController extends Controller
{
    public function saveAnalyse(Request $request)
    {
        $user = $request->get('user');
        $analyse = new analysis();
        $analyse->name = $request->get('name');
        $representation = representation_type::where('name', $request->get('representation_type'))->first();
        if ($representation == null) {
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
        if ($theme_name == null) {
            error_log("missing theme");
            abort(400, "missing theme or theme don't exist");
        }
        $analyse->theme_name = $request->get('theme_name');

        $analyse->save();

        $analyse = analysis::where('name', $request->get('name'))->first();
        $analysis_columns = $request->get('analyse_columns');
        if( $analysis_columns != null ) {
            AnalyseController::createAnalysisColumn($request, $analyse->id);
        }
        return response($analyse)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public static function createAnalysisColumn($analysis_columns, $id)
    {
        $user = $request->get('user');
        $analyse = analysis::where('id', $id);
        for ($i = 0; $i < count($analysis_columns); $i++) {
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
            } catch (Exception $e) {
                error_log('error');
            }
        }
    }

    public function getAnalysisById(Request $request, $id)
    {
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;

        $analysis = analysis::with('fields')->where('id', $id)->first();
        foreach ($analysis->fields as $field) {
            if (array_search(dataset::where('databaseName', $field->databaseName), $datasets) == null) {
                abort(403);
            }
        }
        return response($analysis)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function deleteAnalysis(Request $request, $id)
    {
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;

        $analysis = analysis::where('id', $id)->first();
        if (($analysis->owner_id != $user->uuid && $user->role != "Administrateur") || $analyse != null) {
            abort(403);
        }
        $analysis->delete();
    }

    private function objectLiteToArray($array, string $key = "name")
    {
        $result = [];
        foreach ($array as $element) {
            array_push($result, $element[$key]);
        }
        return $result;
    }

    public function getAllAccessibleAnalysis(Request $request, bool $saved = false)
    {
        $user = $request->get('user');
        $analysis = analysis::with('analysis_columns')->where(function ($query) use ($user) {
            if ($user["role"] == "Administrateur") {
                $query->get();
            } else {
                $query->where('owner_id', $user["uuid"])
                    ->orWhere(function ($query) use ($user) {
                        $query->where('shared', 1)
                            ->where(function ($query) use ($user) {
                                $query->where("visibility", "all");
                                $query->orWhere("visibility", "worker")
                                    ->whereIn("theme_name", $this->objectLiteToArray(user_theme::where("uuid", $user["uuid"])->get("name")));
                                if ($user["role"] == "Référent-Métier") {
                                    $query->orWhere("visibility", "job_referent")
                                        ->whereIn("theme_name", $this->objectLiteToArray(user_theme::where("uuid", $user["uuid"])->get("name")));
                                }
                            });
                    });
            }
        })->where(function ($query) use ($user, $saved) {
            if ($saved) {
                $query->whereIn("id", $this->objectLiteToArray(saved_card::where("uuid", $user["uuid"])->get("id"), "id"));
            }
        })->get();


        $analysis_columns = analysis_column::whereIn("analysis_id", $this->objectLiteToArray($analysis, "id"))->get();
        $sort_column = [];
        foreach ($analysis_columns as $column) {
            if (!array_key_exists($column["analysis_id"], $sort_column)) {
                $sort_column[$column["analysis_id"]] = ["name" => $column["databaseName"], "columns" => []];
            }

            array_push($sort_column[$column["analysis_id"]]["columns"], $column["field"]);
        }

        $validatedID = [];
        foreach (array_keys($sort_column) as $key) {
            $columnToCheck = $sort_column[$key]["columns"];
            $request["columns"] = $columnToCheck;
            $AccessibleColumns = IndexService::checkRights($request, false, $sort_column[$key]["name"]);
            if ($AccessibleColumns != false and count(array_intersect($columnToCheck, $AccessibleColumns)) == count($columnToCheck)) {
                array_push($validatedID, $key);
            }
        }

        $result = [];
        foreach ($analysis as $analyse) {
            if (in_array($analyse["id"], $validatedID)) {
                array_push($result, $analyse);
            }
        }
        return $result;
    }

    public function getAllSavedAnalysis(Request $request)
    {
        $user = $request->get('user');
        $analysis = $this->getAllAccessibleAnalysis($request, true);
        $savedCards = saved_card::where("uuid", $user["uuid"])->whereIn("id", $this->objectLiteToArray($analysis, "id"))->get();

        $result = [];
        foreach ($savedCards as $savedCard) {
            $key = array_search($savedCard["id"], array_column($analysis, 'id'));
            $savedCard["analysis"] = $analysis[$key];
            array_push($result, $savedCard);
        }
        return $result;
    }
}
