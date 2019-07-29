<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;
use App\user;
use Illuminate\Support\Str;
use TrayLabs\InfluxDB\Facades\InfluxDB;

class IndexController extends Controller
{
    public function getAllIndex()
    {
        $stats = Elasticsearch::indices()->stats();
        $indexes = $stats['indices'];
        return response($indexes)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllDateFieldsFromAnIndexFromItsName(Request $request, $name)
    {
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        $datasetId;
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }

        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $data = [
            'index' => $name
        ];
        $return = Elasticsearch::indices()->getMapping($data);

        //dd($return);
        $date_fields = [];
        foreach ($return[$name]['mappings']['doc']['properties'] as $field => $field_data) {
            //dd($field_data['type']);
            if (array_key_exists('type', $field_data) && $field_data['type'] == "date") {
                array_push($date_fields, $field);
            }
            if ($field == "properties") {
                foreach ($field_data["properties"] as $inner_field => $inner_field_data) {
                    if (array_key_exists('type', $inner_field_data) && $inner_field_data['type'] == "date") {
                        array_push($date_fields, "properties" . "." . $inner_field);
                    }
                }
            }
        }
        //dd($date_fields);
        return $date_fields;
    }

    public function getAllFieldsFromIndexByName(Request $request, $name)
    {
        $user = $request->get('user');
        
        if($user->role == "Administrateur")

        /* Should only be used by administrators for validating columns from a dataset
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $datasetId;
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }
        */

        $canAccess = false;
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $data = [
            'index' => $name
        ];
        $return = Elasticsearch::indices()->getMapping($data);
        $count = Elasticsearch::search(['index' => $name, 'size' => 1, 'from' => 0]);
        $fields = [];
        foreach ($return[$name]['mappings']['doc']['properties'] as $field => $field_data) {
            //dd($field_data['type']);
            if ($field == "properties") {
                foreach ($field_data["properties"] as $inner_field => $inner_field_data) {
                    array_push($fields, "properties" . "." . $inner_field);
                }
            } else {
                array_push($fields, $field);
            }
        }
        return ['count' => $count['hits']['total'], 'fields' => $fields];
    }

    public function getAllAccessibleFieldsFromIndexByName(Request $request, $name)
    {
        $user = $request->get('user');
        $canAccess = false;
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $data = [
            'index' => $name
        ];
        $return = Elasticsearch::indices()->getMapping($data);
        $dataset = Dataset::where('databaseName', $name)->first();
        //dd($return);
        $accessibleFields = DatasetController::getAllAccessibleColumnsFromADataset($request, $dataset);
        //dd($accessibleFields);
        $fields = [];
        foreach ($return[$name]['mappings']['doc']['properties'] as $field => $field_data) {
            if ($field == "properties") {
                foreach ($field_data["properties"] as $inner_field => $inner_field_data) {
                    //dd(json_encode($field_data["properties"]));
                    if(!array_key_exists('type', $inner_field_data))
                    {
                        array_push($fields, ['properties.'.$inner_field, 'array']);
                    } else {
                        array_push($fields, ['properties.'.$inner_field, $inner_field_data['type']]);
                        //dd($fields);
                    }
                }
            } else {
                //dd($field_data['type']);
                if(!array_key_exists('type', $field_data))
                {
                    array_push($fields, [$field, 'array']);
                } else {
                    array_push($fields, [$field, $field_data['type']]);
                }
            }
        }

        //dd($fields);

        $results = [];

        foreach($accessibleFields as $acc_field) {
            foreach($fields as $field){
                if($field[0] == $acc_field['name']){
                    array_push($results, $field);
                }
            }
        }

        //dd($date_fields);
        return $results;
    }


    public static function getIndexByNameQuantityAndOffset(Request $request, $name, $quantity = 5, $offset = 0, $date_col = null, $start_date = null, $end_date = null)
    {
        $user = $request->get('user');
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
        $canAccess = false;
        $datasetId;
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
                break;
            }
        }

        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        foreach ($datasets as $dataset) {
            if ($name === $dataset->databaseName) {
                $datasetId = $dataset->id;
                $canAccess = true;
                break;
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, dataset::where('id', $datasetId)->first());
        $columnFilter = [];

        foreach ($columns as $column) {
            array_push($columnFilter, $column->name);
        }
        //dd($columnFilter);
        $body = [];
        if ($date_col != null && $start_date == null && $end_date == null) {
            $body = ['sort' => [[$date_col => ['order' => 'desc']]]];
        } elseif ($date_col != null && $start_date != null && $end_date == null) {
            $body = ['sort' => [$date_col => ['order' => 'desc']], 'query' => ['range' => [$date_col => ['gte' => $start_date, 'lte' => $start_date]]]];
        } elseif ($date_col != null && $start_date != null && $end_date != null) {
            $body = ['sort' => [$date_col => ['order' => 'desc']], 'query' => ['range' => [$date_col => ['gte' => $start_date, 'lte' => $end_date]]]];
        }
        //dd(json_encode([[$date_col => ['order' => 'desc']]]));
        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter, 'size' => $quantity, "from" => $offset, "body" => $body]);
        //error_log(dd($data));
        //$data = Functions::parseIndexJson($data);
        return $data;
    }

    public function getIndexByName(Request $request, $name, $quantity = 5, $offset = 0, $date_col = null, $start_date = null, $end_date = null)
    {
        $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, $quantity, $offset, $date_col, $start_date, $end_date);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getIndexFile(Request $request, $name)
    {
        $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, 1);
        $lineCnt = $data['hits']['total'];
        $file = fopen($databaseName . ".json", "w");
        $iterCount = $lineCnt / 1000;
        for ($i = 0; i < $iterCount; $i++) {
            $data = IndexController::getIndexByNameQuantityAndOffset($request, $name, 1000, i * 1000);
            fwrite($file, $data);
        }
        fclose($file);
        $file->move(public_path() . '/downloads', $dataset->databaseName . '.json');
        $data = "api.local/downloads/" . $dataset->databaseName . '.json';
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getIndexFromCoordinatesInShape(Request $request)
    {
        $filter_dataset = $request->get('filter_dataset');
        $filter_field = $request->get('filter_field');

        $filter_id_field = $request->get('filter_id_field');
        $filter_id = $request->get('filter_id');

        $filtered_dataset = $request->get('filtered_dataset');
        $filtered_field = $request->get('filtered_field');

        //Fetch the geoshape data to be used as a filter
        $body = ['query' => ['match' => [$filter_id_field => $filter_id]]];

        $data = Elasticsearch::search(['index' => $filter_dataset, '_source' => [$filter_id_field, $filter_field], 'size' => 1, 'from' => 0, 'body' => $body]);

        $filter_data = $data['hits']['hits'][0]['_source'][$filter_field];

        $filter = ["query" => ["bool" => ["filter" => ["geo_shape" => [$filtered_field => ["shape" => $filter_data, "relation" => "within"]]]]]];

        $filtered_data = Elasticsearch::search(['index' => $filtered_dataset, 'size' => 1, 'from' => 0, 'body' => $filter]);

        return response($filtered_data)->header('Content-Type', "application/json")->header('charset', 'utf-8');
    }


    public function getLiteIndex(Request $request)
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

        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, $dataset);
        $columnFilter = [];

        $canAccess = false;

        if ($request->get('columns') != null) {
            foreach ($columns as $column) {
                if (in_array($column->name, $request->get('columns'))) {
                    array_push($columnFilter, $column->name);
                    $canAccess = true;
                }
            }
        }
        if (!$canAccess) {
            abort(403);
        }

        $body = [];
        $date_col = $request->get('date_col');
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


        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter,
            'size' => $request->get('size'),
            "from" => $request->get('offset'),
            "body" => $body]);

        return response($data, 200);
    }

}