<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;


use App\auth_users;
use App\colauth_users;
use App\column;
use App\dataset;
use App\dataset_has_representation;
use App\dataset_has_tag;
use App\representation_type;
use App\tag;
use App\theme;
use App\user;
use App\user_saved_dataset;
use App\user_theme;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;


class DatasetController extends Controller
{
    function getAllDatasets(Request $request)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public static function getAllAccessibleDatasets(Request $request, user $user = null, bool $validate = false, bool $saved = false, bool $favorite = false, int $id = null)
    {
        if ($user == null) {
            $user = $request->get('user');
        }

        $datasets = dataset::where(function ($query) use ($user) {
            if ($user["role"] == "Administrateur") {
                $query->get();
            } else {
                $userThemes = DatasetController::objectLiteToArray(user_theme::where("uuid", $user["uuid"])->get("name"));

                $idColumnsByAuth = DatasetController::objectLiteToArray(colauth_users::where("uuid", $user["uuid"])->get("id"), "id");
                $idColumnsByVisibility = DatasetController::objectLiteToArray(column::where(function ($query) use ($user, $userThemes) {
                    $query->where("visibility", "all");
                    $query->orWhere("visibility", "worker")->whereIn("themeName", $userThemes);
                    if ($user["role"] == "Référent-Métier") {
                        $query->orWhere("visibility", "job_referent")->whereIn("themeName", $userThemes);
                    }
                })->get("id"), "id");

                $idDatasetByAuth = DatasetController::objectLiteToArray(auth_users::where("uuid", $user["uuid"])->get("id"), "id");
                $idDatasetByIdColumns = DatasetController::objectLiteToArray(column::whereIn("id", array_merge_recursive($idColumnsByAuth, $idColumnsByVisibility))->get("dataset_id"), "dataset_id");

                $query->whereIn("id", array_merge_recursive($idDatasetByAuth, $idDatasetByIdColumns));
                $query->orWhere("visibility", "all");
                $query->orWhere("visibility", "worker")->whereIn("themeName", $userThemes);
                if ($user["role"] == "Référent-Métier") {
                    $query->orWhere("visibility", "job_referent")->whereIn("themeName", $userThemes);
                }
            }
        })
            ->where("validated", !$validate)
            ->where(function ($query) use ($user, $saved, $favorite, $id) {
                if ($saved) {
                    $query->whereIn("id", DatasetController::objectLiteToArray(user_saved_dataset::where("uuid", $user["uuid"])
                        ->where("favorite", $favorite)->get("id"), "id"));
                }
                if ($id != null) {
                    $query->where("id", $id);
                }
            })->get();

        return ($datasets);
    }

    private static function objectLiteToArray($array, string $key = "name")
    {
        $result = [];
        foreach ($array as $element) {
            array_push($result, $element[$key]);
        }
        return $result;
    }

    public function updateDataset(Request $request)
    {
        $role = $request->get('user')->role;
        if ($role != "Référent-Métier" && $role != "Administrateur") {
            abort(403);
        }
        /*
        $postBody='';
        // Check for presence of a body in the request
        if (count($request->json()->all())) {
            $postBody = $request->json()->all();
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
        if(!$dataset->validate($postBody)){
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


        $dataset->validated = true;

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

        $client = ClientBuilder::create()->setHosts([env("ELASTICSEARCH_SERVICE_HOST") . ":" . env("ELASTICSEARCH_SERVICE_PORT")])->build();
        $paramsSettings = ['index' => $dataset->databaseName,
            'body' => ["index.max_result_window" => 5000000]];
        $client->indices()->putSettings($paramsSettings);

    }

    public function uploadDataset(Request $request)
    {
        $description = $request->get('description');
        $name = $request->get('name');

        $metier = $request->get('metier');


        $creator = $request->get('creator');
        $contributor = $request->get('contributor');
        $dataset = new dataset();
        $dataset->name = $name;
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

    public function getFilterDatasets(Request $request)
    {
        $datasets = DatasetController::getAllAccessibleDatasets($request);

        $filter_datasets = [];
        foreach ($datasets as $dataset) {
            if ($dataset['update_frequency'] != 'Production unique' && $dataset['GEOJSON'] == '0') {
                array_push($filter_datasets, $dataset);
            }
        }

        return $filter_datasets;
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
        $directColumns = $user->columns;
        foreach ($directColumns as $dc) {
            if ($dc->dataset_id == $dataset->id) {
                array_push($array, $dc);
            }
        }
        $columns = $columns->merge($array);
        return $columns;
    }

    public function saveDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset);
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

    public function favoriteDataset(Request $request, $id)
    {
        $dataset = dataset::where('id', $id)->first();
        $user = $request->get('user');
        DatasetController::saveAndFavoriteDataset($user, $dataset, true);
    }

    public function unSaveDataset(Request $request, $id)
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
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false, true, true);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function getDatasetById(Request $request, $id)
    {
        $data = DatasetController::getAllAccessibleDatasets($request, $request->get('user'), false, false, false, $id);
        return response($data)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
