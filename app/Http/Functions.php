<?php


namespace App\Http;


class Functions
{
    static function unicode2html($str){
        // Set the locale to something that's UTF-8 capable
        setlocale(LC_ALL, 'en_US.UTF-8');
        // Convert the codepoints to entities
        $str = preg_replace("/u([0-9a-fA-F]{4})/", "&#x\\1;", $str);
        // Convert the entities to a UTF-8 string
        return iconv("UTF-8", "ISO-8859-1//TRANSLIT", $str);
    }

    static function parseIndexJson($data){
        foreach ($data["hits"]["hits"]  as $key => $array){
            $data["hits"]["hits"][$key]["_source"]["message"] = Functions::unicode2html($data["hits"]["hits"][$key]["_source"]["message"] );
            $data["hits"]["hits"][$key]["_source"]["message"] = str_replace('\\', '', $data["hits"]["hits"][$key]["_source"]["message"]);
            $data["hits"]["hits"][$key]["_source"] = str_replace("\\\"", '"', $data["hits"]["hits"][$key]["_source"]);
            $data["hits"]["hits"][$key]["_source"]["message"]  = substr($data["hits"]["hits"][$key]["_source"]["message"] , 1);
            $data["hits"]["hits"][$key]["_source"]["message"]  = substr_replace($data["hits"]["hits"][$key]["_source"]["message"]  ,"", -1);
            $data["hits"]["hits"][$key]["_source"]["message"] = html_entity_decode($data["hits"]["hits"][$key]["_source"]["message"]);
            $data["hits"]["hits"][$key]["_source"]["message"] = json_decode($data["hits"]["hits"][$key]["_source"]["message"]);
        }
        return $data;
    }
}