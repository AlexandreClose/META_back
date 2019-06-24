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
            foreach($data["hits"]["hits"][$key]["_source"] as $key2 => $array2)
            {
                $str_to_change = Functions::unicode2html($str_to_change );
                $str_to_change = str_replace('\\', '', $str_to_change);
                $str_to_change = str_replace("\\\"", '"', $str_to_change);
                $str_to_change = substr($str_to_change , 1);
                $str_to_change = substr_replace($str_to_change  ,"", -1);
                $str_to_change = html_entity_decode($str_to_change);
                $str_to_change = json_decode($str_to_change);
            }
            
        }
        return $data;
    }
}