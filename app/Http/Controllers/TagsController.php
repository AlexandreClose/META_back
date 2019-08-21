<?php /** @noinspection PhpUnused */

namespace App\Http\Controllers;


use App\tag;

class TagsController extends Controller
{
    public function getAllTags()
    {
        return tag::all();
    }
}
