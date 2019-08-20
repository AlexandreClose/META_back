<?php

namespace App\Http\Controllers;

use Elasticsearch\ClientBuilder;
use http\Env\Response;
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
use GuzzleHttp;

class DatasetController extends Controller
{
    function getAllDatasets(Request $request, $quantity = null, $offset = null)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function updateDataset(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Référent-Métier" && $role != "Administrateur") {
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
        if ($request->get('id') != null) {
            $dataset = dataset::where('id', '=', $request->get('id'))->first();
        }
        if ($dataset == null) {
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


        $visibility = $request->get('visibility');
        $theme = $request->get('theme');
        $JSON = (bool)$request->get('JSON');
        $GEOJSON = (bool)$request->get('GEOJSON');

        $dataset->name = $name;
        $dataset->description = $description;

        $dataset->producer = $producer;
        $dataset->license = $license;
        $dataset->created_date = $created_date;
        $dataset->creator = $creator;
        $dataset->contributor = $contributor;
        $dataset->update_frequency = $frequency;

        $dataset->visibility = $visibility;
        $theme_from_base = theme::where('name', $theme)->first();
        if ($theme_from_base == null) {
            error_log($theme_from_base);
            abort(400);
        }

        $dataset->GEOJSON = $GEOJSON;
        $dataset->JSON = $JSON;
        $dataset->validated = true;
        $result = $dataset->save();

        $dataset = dataset::where('id', $request->get('id'))->first();
        $tags = json_decode($tags);
        if ($tags != null) {
            error_log("tags not null");
            foreach ($tags as $tag) {
                error_log("tag array");
                $_tag = tag::where('name', $tag)->first();
                if ($_tag == null) {
                    error_log("Créer un nouveau tag");
                    $_tag = new tag();
                    $_tag->name = $tag;
                    $_tag->save();
                }
                error_log("Créer la relation entre " . $dataset->name . " et " . $_tag->name);
                if ((dataset_has_tag::where('id', $dataset->id)->where('name', $_tag->name)->first() == null)) {
                    $dataset_tag = new dataset_has_tag();
                    $dataset_tag->id = $dataset->id;
                    $dataset_tag->name = $_tag->name;
                    $dataset_tag->save();
                }
            }
        }
        error_log("first foreach passed");
        $visualisations = $request->get('visualisations');
        $visualisations = json_decode($visualisations);
        foreach ($visualisations as $visualisation) {
            $type = representation_type::where('name', $visualisation)->first();
            if ((dataset_has_representation::where('representationName', $type->name)->where('datasetId', $request->get('id'))->first()) == null) {
                $types = new dataset_has_representation();
                $types->datasetId = $request->get('id');
                $types->representationName = $type->name;
                $types->save();
            }
        }
        error_log("second foreach passed");
        $users = $request->get('users');
        $users = json_decode($users);
        foreach ($users as $user_id) {
            $auth_user = user::where('uuid', $user_id)->first();
            if ($auth_user == null || ((auth_users::where('uuid', $auth_user->uuid)->where('id', $request->get('id'))->first()) != null)) {
                continue;
            }
            $auth_users = new auth_users();
            $auth_users->id = $request->get('id');
            $auth_users->uuid = $auth_user->uuid;
            $auth_users->save();
        }
        error_log("last foreach passed");

        $client = ClientBuilder::create()->setHosts([env("ELASTICSEARCH_HOST") . ":" . env("ELASTICSEARCH_PORT")])->build();
        $paramsSettings = ['index' => $dataset->databaseName,
            'body' => ["index.max_result_window" => 5000000]];
        $client->indices()->putSettings($paramsSettings);

    }


    public function uploadDataset(Request $request)
    {
        $description = $request->get('description');
        $name = $request->get('name');
        $tags = $request->get('tag');
        $metier = $request->get('metier');
        $JSON = $request->get('JSON');
        $GEOJSON = $request->get('GEOJSON');
        //$util = $request->get('utils');
        $visualisations = $request->get('visualisations');
        $visualisations = json_decode($visualisations);
        $date = $request->get('date');
        $creator = $request->get('creator');
        $contributor = $request->get('contributor');
        $dataset = new dataset();
        $dataset->name = $name;
        $dataset->JSON = (bool)$JSON;
        $dataset->GEOJSON = (bool)$GEOJSON;
        //$dataset->util = $util;
        $dataset->validated = false;
        $dataset->description = $description;
        $dataset->creator = $creator;
        $dataset->contributor = $contributor;
        $dataset->license = "Fermée";
        $dataset->created_date = Carbon::now();
        $dataset->updated_date = Carbon::now();
        $dataset->realtime = false;
        $dataset->conf_ready = false;
        $dataset->upload_ready = false;
        $dataset->open_data = false;
        $dataset->visibility = "job_referent";
        $dataset->user = $creator;
        $dataset->producer = $creator;
        $dataset->themeName = $metier;
        $dataset->databaseName = str_replace("-", "_", Str::slug($name));
        $file = $request->file('uploadFile');
        $file->move(storage_path() . '/uploads', $dataset->databaseName . '.' . $file->getClientOriginalExtension());
        $theme = theme::where('name', $metier)->first();

        if ($theme == null) {
            error_log($theme);
            error_log($metier);
            abort(400);
        }
        $dataset->save();
        $dataset = dataset::where('name', $name)->first();
        /*
        foreach($visualisations as $visualisation){
            $type = representation_type::where('name', $visualisation)->first();
            if((dataset_has_representation::where('representationName', $type->name)->where('datasetId', $dataset->id)->first()) == null){
                $types = new dataset_has_representation();
                $types->datasetId = $request->get('id');
                $types->representationName = $type->name;
                $types->save();
            }
        }*/

    }

    public function getDatasetsToValidate(Request $request)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getFilterDatasets(Request $request){
        $datasets = DatasetController::getAllAccessibleDatasets($request);

        $filter_datasets = [];
        foreach($dataset as $datasets){
            if($dataset['update_frequency'] != 'Production unique' && $dataset['GEOJSON'] == '0'){
                array_push($filter_datasets, $dataset);
            }
        }

        return $filter_datasets;
    }

    public static function getAllAccessibleDatasets(Request $request, user $user = null, bool $validate = false, bool $saved = false, bool $favorite = false, $id = null)
    {
        if ($user == null) {
            $user = $request->get('user');
        }
        $themes = $user->themes;
        $role = $user->role;
        $directdatasets = DB::select("SELECT ds.*, IF(usd.id IS NULL, 0, 1) as saved, IFNULL(usd.favorite, 0) as favorite
        FROM metacity.datasets ds 
        JOIN metacity.auth_users au
        ON au.id = ds.id
        LEFT JOIN metacity.user_saved_datasets usd 
        ON ds.id = usd.id 
        WHERE (usd.uuid = '" . $user->uuid . "'
        OR usd.uuid IS NULL)".($id != null ? " AND ds.id == ".$id : "")."
        ORDER BY created_date DESC");
        $querybase = "SELECT ds.*, IF(usd.id IS NULL, 0, 1) as saved, IFNULL(usd.favorite, 0) as favorite
        FROM metacity.datasets ds 
        LEFT JOIN metacity.user_saved_datasets usd 
        ON ds.id = usd.id\n";
        $where = "WHERE " . ($saved || $favorite ? "usd.uuid = '" . $user->uuid . "'" : "(usd.uuid = '" . $user->uuid . "' OR usd.uuid IS NULL)") . "
        AND ds.validated = " . ($validate ? 0 : 1) . "
        AND ds.conf_ready = 1"
        .($id != null ? " AND ds.id == ".$id : "")."
        AND upload_ready = 1\n";
        $where = $where . ($saved || $favorite ? "AND usd.favorite = " . ($favorite ? 1 : 0) . "\n" : "");
        //$where = "";
        switch ($user->role) {
            case "Administrateur":
                $datasets = [];
                break;
            case "Référent-Métier":
                $where = $where . "AND ((ds.visibility IN ('worker', 'job_referent') AND ds.themeName = '" . $user->theme . "') OR ds.visibility = 'all')\n";
                $datasets = $directdatasets;
                $datasets = array_merge($datasets, (DB::select("SELECT ds.*, IF(usd.id IS NULL, 0, 1) as saved, IFNULL(usd.favorite, 0) as favorite
                                            FROM (SELECT dsi.* 
                                            FROM metacity.datasets dsi 
                                            JOIN metacity.columns c 
                                            ON c.dataset_id = dsi.id 
                                            LEFT OUTER JOIN metacity.colauth_users 
                                            ON c.id = colauth_users.id 
                                            WHERE colauth_users.uuid = '" . $user->uuid . "'
                                            OR (colauth_users.uuid  IS NULL 
                                            AND (c.visibility IN ('worker', 'job_referent') AND c.themeName = '" . $user->theme . "') 
                                            OR c.visibility = 'all')
                                            GROUP BY dsi.id) ds 
                LEFT JOIN metacity.user_saved_datasets usd 
                ON ds.id = usd.id\n" . $where . "ORDER BY created_date DESC")));
                break;
            case "Utilisateur":
                $where = $where . "AND ((ds.visibility IN ('worker') AND ds.themeName = '" . $user->theme . "') OR ds.visibility = 'all')\n";
                $datasets = $directdatasets;
                $datasets = array_merge($datasets, (DB::select("SELECT ds.*, IF(usd.id IS NULL, 0, 1) as saved, IFNULL(usd.favorite, 0) as favorite
                    FROM (SELECT dsi.* 
                                            FROM metacity.datasets dsi 
                                            JOIN metacity.columns c 
                                            ON c.dataset_id = dsi.id 
                                            LEFT OUTER JOIN metacity.colauth_users 
                                            ON c.id = colauth_users.id 
                                            WHERE colauth_users.uuid = '" . $user->uuid . "'
                                            OR (colauth_users.uuid  IS NULL 
                                            AND (c.visibility IN ('worker') AND c.themeName = '" . $user->theme . "') 
                                            OR c.visibility = 'all')
                                            GROUP BY dsi.id) ds 
                    LEFT JOIN metacity.user_saved_datasets usd 
                    ON ds.id = usd.id\n" . $where . "ORDER BY created_date DESC")));
                break;
            default:
                $datasets = [];
                return $datasets;
        }
        $where = $where . "ORDER BY created_date DESC";
        $query = $querybase . $where;
        //error_log($query);
        $datasets = array_merge($datasets, DB::select($query));
        foreach ($datasets as $dataset) {
            $fromBase = Dataset::where('id', $dataset->id)->first();
            //dd($dataset);
            $dataset->representations = $fromBase->representations;
            $dataset->tags = $fromBase->tags;
        }
        return $datasets;
    }

    public function getRepresentationsOfDataset($id)
    {
        $dataset = dataset::where('id', $id)->first();
        if ($dataset == null) {
            abort(404);
        }
        $representations = $dataset->representations;
        return response($representations)->header('Content-Type', 'application/json')->header('charset', 'utf-8');

    }

    public function getAllColumnFromDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $columns = DatasetController::getAllAccessibleColumnsFromADataset($request, $dataset);
        return response($columns)->header('Content-Type', 'application/json')->header('charset', 'utf-8');

    }

    public static function getAllAccessibleColumnsFromADataset(Request $request, Dataset $dataset)
    {
        $user = $request->get('user');
        $role = $user->role;
        $themes = [];

        foreach ($user['themes'] as $t) {
            array_push($themes, $t['name']);
        }

        switch ($role) {
            case "Administrateur":
                $columns = column::where('dataset_id', $dataset->id)->get();
                break;
            case "Référent-Métier":
                $columns = column::where('dataset_id', $dataset->id)->whereIn('themeName', $themes)->whereIn('visibility', ['job_referent', 'worker'])->get();
                $columns->merge(column::where('dataset_id', $dataset->id)->whereIn('visibility', ['all', null]));
                break;
            case "Utilisateur":
                $columns = column::where('dataset_id', $dataset->id)->where('visibility', 'worker')->where('themeName', $themes)->get();
                $columns->merge(column::where('dataset_id', $dataset->id)->whereIn('visibility', ['all', null]));
                break;
            default:
                $columns = [];
                break;
        }
        $array = [];
        $directcolumns = $user->columns;
        foreach ($directcolumns as $dc) {
            if ($dc->dataset_id == $dataset->id) {
                array_push($array, $dc);
            }
        }
        $columns = $columns->merge($array);
        return $columns;
    }


    public static function saveAndFavoriteDataset(user $user, dataset $dataset, $favorite = false)
    {
        $saved_ds = user_saved_dataset::where('uuid', $user->uuid)->where('id', $dataset->id)->first();
        if ($saved_ds == null) {
            $saved_ds = new user_saved_dataset();
            $saved_ds->id = $dataset->id;
            $saved_ds->uuid = $user->uuid;
        }
        $saved_ds->favorite = $favorite;
        $saved_ds->save();
    }

    public function saveDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset);
    }

    public function favoriteDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset, true);
    }

    public function unsaveDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        $saved_ds = user_saved_dataset::where('uuid', $user->uuid)->where('id', $dataset->id)->first();
        if ($saved_ds != null) {
            $saved_ds->delete();
        }
    }

    public function getAllAccessibleSavedDatasets(Request $request)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false, true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getAllAccessibleFavoriteDatasets(Request $request)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false, false, true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getDatasetById(Request $request, $id){
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false, false, false, $id);
        return response($data[0])->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
