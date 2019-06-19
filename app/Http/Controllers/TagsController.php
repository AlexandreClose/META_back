<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\tag;

class TagsController extends Controller
{
    public function getAllTags(){
        return tag::all();
    }
}
