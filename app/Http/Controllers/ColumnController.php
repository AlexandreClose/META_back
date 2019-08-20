<?php

namespace App\Http\Controllers;

use App\Http\Services\ColumnService;
use App\Http\Services\ElasticSearchService;
use App\Http\Services\IndexService;
use App\Http\Services\InfluxDBService;
use DateTime;
use Elasticsearch\ClientBuilder;
use Exception as ExceptionAlias;
use Illuminate\Http\Request;
use App\column;
use App\dataset;
use App\theme;
use App\data_type;
use App\user;
use App\colauth_users;
use Elasticsearch;
use InfluxDB\Client;

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

            if ((bool)$column->main and $fields[$column->name] == "text" and $column->name != "geometry") {
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
        #todo validation
        return (new ColumnService)->getStatsService($request);
    }
}
