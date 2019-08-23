<?php


namespace App\Http\Services;


use DateTime;
use Exception as ExceptionAlias;

class IndexColumnService
{
    public static function do_stats(array $columns, array $data)
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
                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, 0);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    $element["stats"][$column] = [
                        "min" => $pathData,
                        "max" => $pathData,
                        "avg" => $pathData,
                        "sum" => $pathData,
                        "count" => 1,
                        "DiffOcc" => $result["Count"],
                        "DiffSum" => $pathData,
                        "DiffAvg" => $pathData];
                    $stats[$pathPivot] = $element;

                } else {
                    $s = $stats[$pathPivot];
                    $oldStats = $s["stats"][$column];
                    array_merge_recursive($stats[$pathPivot], $element);

                    $result = IndexColumnService::diff_occurrences($occurrences[$pathPivot], $pathData, $oldStats["DiffOcc"]);
                    $occurrences[$pathPivot] = $result["Occurrences"];

                    if ($result["isSum"]) {
                        $oldStats["DiffSum"] += $pathData;
                        $oldStats["DiffAvg"] = ($oldStats["DiffAvg"] + $pathData) / 2;
                    }

                    $stats[$pathPivot]["stats"][$column] = [
                        "min" => min($pathData, $oldStats["min"]),
                        "max" => max($pathData, $oldStats["max"]),
                        "avg" => ($pathData + $oldStats["avg"]) / 2,
                        "sum" => ($pathData + $oldStats["sum"]),
                        "count" => ($oldStats["count"] + 1),
                        "DiffOcc" => ($result["Count"]),
                        "DiffSum" => $oldStats["DiffSum"],
                        "DiffAvg" => $oldStats["DiffAvg"]];
                }
            }
        }
        return $stats;
    }

    private static function diff_occurrences(array $occurrences, $element, $i)
    {
        $isSum = false;
        if (!in_array($element, $occurrences)) {
            array_push($occurrences, $element);
            $i++;
            $isSum = true;
        }
        return ["Count" => $i, "isSum" => $isSum, "Occurrences" => $occurrences];
    }
}
