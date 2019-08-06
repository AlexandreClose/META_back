<?php

namespace App\Http\Controllers;

use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use App\column;
use App\dataset;
use App\theme;
use App\data_type;
use App\user;
use App\colauth_users;
use Elasticsearch;

class ColumnController extends Controller
{
    function createColumn(Request $request)
    {
        $client = ClientBuilder::create()->setHosts([env("ELASTICSEARCH_HOST") . ":" . env("ELASTICSEARCH_PORT")])->build();

        $role = $request->get('user')->role;
        if ($role != "Référent-Métier" && $role != "Administrateur") {
            abort(403);
        }
        $postbody = "";
        if (count($request->json()->all())) {
            $postbody = $request->json()->all();
        } else {
            error_log("no body in requests");
            abort(400);
        }


        $columns = [];
        foreach ($postbody as $element) {
            $dataset = dataset::where('id', '=', $element["datasetId"])->first();
            if ($dataset === null) {
                error_log("no dataset with that id");
                abort(404);
            }

            if ($element["name"] == null || $element["datasetId"] == null) {
                error_log("missing name, datatype or datasetId");
                abort(400);
            }

            $verif = column::where('dataset_id', '=', $element["datasetId"])->where('name', '=', $element['name'])->get();
            if (count($verif) > 0) {
                error_log("column already exists");
                abort(409);
            }
            $column = new column();
            $column->name = $element["name"];
            $column->main = isset($element["main"]) ? $element['main'] : false;
            /* Now we use directly the datatypes from elasticsearch
            $datatype = data_type::where('name', $element['datatype']);
            if ($datatype == null) {
                error_log($datatype);
                error_log(element['datatype']);
                abort(400);
            }
            $column->data_type_name = $element["datatype"];
            */
            $column->visibility = $element["visibility"] == "" ? dataset::select('visibility')->where("id", $column->dataset_id)->first()['visibility'] : $element['visibility'];
            //error_log($column->visibility);
            $column->dataset_id = $element["datasetId"];
            $theme = theme::where('name', $element["theme"])->first();
            if ($theme == null && ($element['theme'] != null || $element['theme'] != "")) {
                error_log($theme);
                error_log($element["theme"]);
                abort(400);
            } elseif ($element["theme"] == null) {
                $column->themeName = dataset::select('themeName')->where("id", $column->dataset_id)->first()['themeName'];
            } else {
                $column->themeName = $element["theme"];
            }

            $column->save();
            $users = $element['users'];
            $column = column::where('name', $element["name"])->where('dataset_id', $element["datasetId"])->first();
            foreach ($users as $user_id) {
                $auth_user = user::where('uuid', $user_id)->first();
                if ($auth_user == null) {
                    continue;
                }
                $auth_users = new colauth_users();
                $auth_users->id = $column->id;
                $auth_users->uuid = $auth_user->uuid;
                $auth_users->save();
            }

            $fields = IndexController::getFieldsAndType($request, $dataset->databaseName);

            if ((bool)$column->main and $fields[$column->name] != "Date") {
                $paramsSettings = ['index' => $dataset->databaseName,
                    'body' => ["index.blocks.read_only_allow_delete" => false]];

                $paramsMapping = ['index' => $dataset->databaseName, 'type' => 'doc',
                    'body' => ['properties' => [$column->name => ['type' => 'text', 'fielddata' => true]]]];

                $client->indices()->putSettings($paramsSettings);
                $client->indices()->putMapping($paramsMapping);
            }
        }
    }

    public function getStats(Request $request)
    {
        $name = $request->get('name');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false);
        $canAccess = false;
        $datasetId = null;
        $dataset = null;

        foreach ($datasets as $data) {
            if ($name === $data->databaseName) {
                $dataset = $data;
                $canAccess = true;
                break;
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $AccessibleColumns = DatasetController::getAllAccessibleColumnsFromADataset($request, dataset::where('databaseName', $name)->first());

        $columns = [];
        $canAccess = false;

        if ($request->get('columns') != null) {
            foreach ($AccessibleColumns as $column) {
                if (in_array($column->name, $request->get('columns'))) {
                    array_push($columns, $column->name);
                    $canAccess = true;
                }
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $body = [];
        $date_col = $request->get('date_col');
        $group_by_column = $request->get('groupby');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $week_day = $request->get('weekdays');
        $emptyDayQuery = "doc['" . $date_col . "'].date.dayOfWeek == ";
        $fullDayQuery = "";
        $start_hour = $request->get('start_hour');
        $end_hour = $request->get('end_hour');
        if ($start_hour != null && $start_hour != null) {
            $hourQuery = "(doc['" . $date_col . "'].date.getHourOfDay() >= " . $start_hour . " && doc['" . $date_col . "'].date.getHourOfDay() < " . $end_hour . ")";
        }
        if ($week_day != null) {
            foreach ($week_day as $day) {
                $fullDayQuery .= $emptyDayQuery . $day . " || ";
            }
            $fullDayQuery = str_replace(" || )", ")", "(" . $fullDayQuery . ")");
        }


        if ($date_col != null) {
            $body = ['sort' => [[$date_col => ['order' => 'desc']]]];
            if ($date_col != null && $start_date != null && $end_date == null) {
                $body["query"]["bool"]["must"] = ['range' => [$date_col => ['gte' => $start_date, 'lte' => $start_date]]];
            } elseif ($date_col != null && $start_date != null && $end_date != null) {
                $body["query"]["bool"]["must"] = ['range' => [$date_col => ['gte' => $start_date, 'lte' => $end_date]]];
            }

            if ($week_day != null && ($start_hour != null && $end_hour != null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => "(" . $fullDayQuery . " && " . $hourQuery . ")"]];
            } elseif ($week_day != null && ($start_hour == null && $end_hour == null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => $fullDayQuery]];
            } elseif ($week_day == null && ($start_hour != null && $end_hour != null)) {
                $body["query"]["bool"]["filter"] = ['script' => ['script' => $hourQuery]];
            }
        }

        $aggs = [];
        foreach ($columns as $column) {
            $aggs[$column] = ["stats" => ["field" => $column]];
        }

        if ($group_by_column) {
            $body["aggs"] = ["codes" => ["terms" => ["field" => $group_by_column, "size" => 10000], "aggs" => $aggs]];
        } else {
            $body["aggs"] = $aggs;
        }

        $data = Elasticsearch::search(['index' => $name,
            'size' => 0,
            "body" => $body]);

        return response($data, 200);
    }
}
