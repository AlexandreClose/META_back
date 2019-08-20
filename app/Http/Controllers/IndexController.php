<?php

namespace App\Http\Controllers;

use App\column;

use App\Http\Services\ElasticSearchService;
use App\Http\Services\IndexService;
use App\Http\Services\InfluxDBService;
use DateTime;
use Elasticsearch;
use App\Http\Functions;
use App\dataset;
use App\user;
use Elasticsearch\ClientBuilder;
use Exception as ExceptionAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InfluxDB\Client;
use PhpParser\Node\Expr\Array_;

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
        $checkRights = (new IndexService)->checkRightsOnDataset($request, false);
        if ($checkRights == false) {
            $columns = null;
            abort(403);
        }

        $checkRights = (new IndexService)->checkRightsOnDataset($request, true);
        if ($checkRights == false) {
            $columns = null;
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
            if (gettype($field_data) == "array" && !array_key_exists('type', $field_data) && $field != "geometry") {
                foreach ($field_data['properties'] as $inner_field => $inner_field_data) {
                    array_push($date_fields, $field . "." . $inner_field);

                }
            }
        }
        //dd($date_fields);
        return $date_fields;
    }

    public static function getFieldsAndType(Request $request, $name)
    {
        $user = $request->get('user');
        $canAccess = false;
        $datasets = DatasetController::getAllAccessibleDatasets($request, $user, true);
        $datasets = array_merge($datasets, DatasetController::getAllAccessibleDatasets($request, $user, false));

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
            if (gettype($field_data) == "array" && !array_key_exists('type', $field_data) && $field != "geometry") {
                foreach ($field_data['properties'] as $inner_field => $inner_field_data) {
                    //dd(json_encode($field_data["properties"]));
                    if (!array_key_exists('type', $inner_field_data)) {
                        //dd($field_data);
                        $fields[$field . $inner_field] = 'array';
                    } else {
                        $fields[$field . '.' . $inner_field] = $inner_field_data['type'];
                        //dd($fields);
                    }
                }
            } else if ($field != "geometry") {
                $fields[$field] = "array";
            } else {
                $fields[$field] = $field_data['properties']["type"]["type"];
            }
        }
        return $fields;
    }

    public function getAllFieldsFromIndexByName(Request $request, $name)
    {
        $user = $request->get('user');

        if ($user->role == "Administrateur")
            /*
            $datasets = DatasetController::getAllAccessibleDatasets($request, $user, false);
            $datasetId;
            foreach ($datasets as $dataset) {
                if ($name === $dataset->databaseName) {
                    $datasetId = $dataset->id;
                    $canAccess = true;
                }
            }*/

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
            if (gettype($field_data) == "array" && !array_key_exists('type', $field_data) && $field != "geometry") {
                foreach ($field_data['properties'] as $inner_field => $inner_field_data) {
                    array_push($fields, $field . "." . $inner_field);
                }
            } else {
                array_push($fields, $field);
            }
        }
        return ['count' => $count['hits']['total'], 'fields' => $fields];
    }

    public function getAllAccessibleFieldsFromIndexByName(Request $request, $name)
    {
        $checkRights = (new IndexService)->checkRightsOnDataset($request, false);
        if ($checkRights == false) {
            $columns = null;
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
            if (gettype($field_data) == "array" && !array_key_exists('type', $field_data) && $field != "geometry") {
                foreach ($field_data['properties'] as $inner_field => $inner_field_data) {
                    //dd(json_encode($field_data["properties"]));
                    if (!array_key_exists('type', $inner_field_data)) {
                        //dd($field_data);
                        array_push($fields, [$field . $inner_field, 'array']);
                    } else {
                        array_push($fields, [$field . '.' . $inner_field, $inner_field_data['type']]);
                        //dd($fields);
                    }
                }
            } else if ($field != "geometry") {
                array_push($fields, [$field, "array"]);
            } else {
                array_push($fields, [$field, $field_data['properties']["type"]["type"]]);
            }
        }


        //dd($fields);

        $results = [];

        foreach ($accessibleFields as $acc_field) {
            foreach ($fields as $field) {
                if ($field[0] == $acc_field['name']) {
                    array_push($field, $acc_field['main']);
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
        $checkRights = (new IndexService)->checkRights($request, false);
        if ($checkRights == false) {
            $columnFilter = null;
            abort(403);
        } else {
            $columnFilter = $checkRights;
        }

        $name = $request->get("name");
        if ((bool)dataset::select('realtime')->where('databaseName', $name)->first()["realtime"]) {
            $request["columns"] = $columnFilter;
            $data = $this::getLiteIndexInflux($request);
            return response($data, 200);
        }

        $ElasticSearchService = new ElasticSearchService($request);

        $minuteQuery = $ElasticSearchService->getMinuteFilter();
        $fullDayQuery = $ElasticSearchService->getWeekdayFilter();

        $body = $ElasticSearchService->getTimeFilter([], $minuteQuery, $fullDayQuery);


        $data = Elasticsearch::search(['index' => $name, '_source' => $columnFilter,
            'size' => $request->get('size'),
            "from" => $request->get('offset'),
            "body" => $body]);

        return response($data, 200);
    }

    private function diff_occurrences(array $occurrences, $element, $i)
    {
        if (!in_array($element, $occurrences)) {
            array_push($occurrences, $element);
            $i++;
        }
        return ["Count" => $i, "Occurrences" => $occurrences];
    }

    public function do_stats(array $columns, array $data)
    {
        $stats = [];
        foreach ($columns["data"] as $column) {
            $occurrences = [];
            foreach ($data as $element) {
                $pathData = $element;
                $tmp = [];
                foreach (explode("+", $columns["pivot"]) as $col) {
                    $pathPivot = $element;
                    foreach (explode(".", $col) as $field) {
                        $pathPivot = $pathPivot[$field];
                    }
                    array_push($tmp, $pathPivot);
                }
                $pathPivot = implode("+", $tmp);

                if ($columns["isDate"]) {
                    try {
                        $d = new DateTime($pathPivot);
                        $pathPivot = date('Y-m-d\TH:i:s.Z\Z', floor($d->getTimestamp() / ($columns["step"] * 3600)) * ($columns["step"] * 3600));
                    } catch (ExceptionAlias $e) {
                    }
                }

                foreach (explode(".", $column) as $field) {
                    $pathData = $pathData[$field];
                }
                $pathData = (float)$pathData;
                if (!array_key_exists($pathPivot, $stats) or !array_key_exists($column, $stats[$pathPivot]["stats"])) {
                    if (array_key_exists($pathPivot, $stats)) {
                        $element = $stats[$pathPivot];
                    }
                    $occurrences[$pathPivot] = [];
                    $result = $this->diff_occurrences($occurrences[$pathPivot], $pathData, 0);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $element["stats"][$column] = [
                        "min" => $pathData,
                        "max" => $pathData,
                        "avg" => $pathData,
                        "sum" => $pathData,
                        "count" => 1,
                        "DiffOcc" => $result["Count"]];
                    $stats[$pathPivot] = $element;

                } else {
                    $s = $stats[$pathPivot];
                    $oldStats = $s["stats"][$column];
                    array_merge_recursive($stats[$pathPivot], $element);

                    $result = $this->diff_occurrences($occurrences[$pathPivot], $pathData, $oldStats["DiffOcc"]);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $stats[$pathPivot]["stats"][$column] = [
                        "min" => min($pathData, $oldStats["min"]),
                        "max" => max($pathData, $oldStats["max"]),
                        "avg" => ($pathData + $oldStats["avg"]) / 2,
                        "sum" => ($pathData + $oldStats["sum"]),
                        "count" => ($oldStats["count"] + 1),
                        "DiffOcc" => ($result["Count"])];
                }
            }
        }
        return $stats;
    }

    private function do_join(int $i, array $data, array $columns)
    {
        $newData = [];
        foreach ($data[$i - 1] as $entry) {
            $path = $entry;
            foreach (explode(".", $columns[0]) as $field) {
                $path = $path[$field];
            }
            foreach ($data[$i] as $newEntry) {
                $newPath = $newEntry;
                foreach (explode(".", $columns[1]) as $field) {
                    $newPath = $newPath[$field];
                }
                if ($path == $newPath) {
                    array_push($newData, array_replace($entry, $newEntry));
                }
            }
        }
        $data[$i] = $newData;
        return $data;
    }

    public function join(Request $request)
    {
        $data = [];
        $datasets = $request["datasets"];

        for ($i = 0; $i < sizeof($datasets); $i++) {
            $body = $datasets[$i];
            $body["user"] = $request["user"];
            $subRequest = new Request($body);
            $results = $this::getLiteIndex($subRequest)->getOriginalContent()["hits"]["hits"];
            $temp = [];
            foreach ($results as $result) {
                array_push($temp, $result["_source"]);
            }
            array_push($data, $temp);
            if ($i >= 1) {
                $columns = $request["joining"][$i - 1];
                $data = $this::do_join($i, $data, $columns);
            }
        }

        if (!$request["stats"]["do_stats"]) {
            return response($data[sizeof($datasets) - 1], 200);
        } else {
            return response($this::do_stats($request["stats"]["columns"]
                , $data[sizeof($datasets) - 1]), 200);
        }
    }

    public function getInPointInPolygon(Request $request)
    {
        $checkRights = (new IndexService)->checkRights($request, false);
        if ($checkRights == false) {
            abort(403);
        } else {
            $columns = $checkRights;
        }


        $nameFilter = $request->get('nameFilter');

        $dataFilters = Elasticsearch::search(['index' => $nameFilter, '_source' => $request->get('filterColumn'),
            'size' => $request->get('size'),
            "from" => $request->get('offset')])["hits"]["hits"];

        $doStats = (bool)$request->get('stats')["do_stats"];
        $result = [];
        $keyId = 1;
        foreach ($dataFilters as $dataFilter) {
            $path = $dataFilter["_source"];
            foreach (explode(".", $request->get('targetColumn')) as $field) {
                $path = $path[$field];
            }
            $polygon = $path[0][0];

            $body = ["query" => ["bool" => [
                "must" => ["match_all" => (object)null],
                "filter" => ["geo_polygon" => ["geometry.coordinates" => ["points" => $polygon]]]]]];


            $name = $request->get('name');
            $data = Elasticsearch::search(['index' => $name, '_source' => $columns,
                'size' => $request->get('size'),
                "from" => $request->get('offset'),
                "body" => $body]);


            if ($doStats) {
                foreach ($data["hits"]["hits"] as $element) {
                    $element = $element["_source"];
                    $element["KeyId"] = $keyId;
                    $element["geometry"]["coordinates"] = $polygon;
                    array_push($result, $element);
                }
                $keyId++;
            } else {
                $NewData = [];
                foreach ($data["hits"]["hits"] as $element) {
                    $element = $element["_source"];
                    array_push($NewData, $element);
                }
                array_push($result, $NewData);
            }
        }

        $doJoin = (bool)$request->get('join')["do_join"];
        if ($doJoin) {
            $subRequest = $request["join"]["request"];
            $subRequest["stats"]["do_stats"] = false;
            $subRequest["user"] = $request->get("user");
            $subRequest = new Request($subRequest);
            $subResult = $this::join($subRequest)->getOriginalContent();
            $result = $this::do_join(1, [$subResult, $result], $request["join"]["joining"])[1];
        }
        if ($doStats) {
            $columns = $request->get("stats")["columns"];
            $columns["pivot"] = "KeyId";
            $columns["isDate"] = false;
            $result = $this::do_stats($columns, $result);
        }
        return response($result);
    }

    private function getLiteIndexInflux(Request $request)
    {
        $result = (new InfluxDBService)->doFullQuery($request);

        $hits = [];
        foreach ($result as $element) {
            $hit = ["_index" => $request["name"], "_source" => $element];
            array_push($hits, $hit);
        }
        $result = ["hits" => ["total" => sizeof($result), "hits" => $hits]];
        return $result;
    }

    public function getLast(Request $request)
    {
        if ((new IndexService)->checkRights($request, false) == false) {
            abort(403);
        }

        $client = (new InfluxDBService)->getClient();

        $select = 'last("' . implode('"), last("', $request["columns"]) . '")';
        $from = $request["name"];
        $groupBy = '"' . implode('", "', explode("+", $request["groupby"])) . '"';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $client->query(env("INFLUXDB_DBNAME"), 'SELECT ' . $select . ' FROM ' . $from . ' GROUP BY ' . $groupBy)->getPoints();

        return response($result, 200);
    }
}
