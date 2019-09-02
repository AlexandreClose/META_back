<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\saved_card;

class SavedCardsController extends Controller
{
    public function getAllSavedCards(Request $request){
        $user = $request->get('user');
        $saved_cards = saved_card::where('user_uuid', $user->uuid)->get();
        foreach ($saved_cards as $saved_card) {
            //$saved_card->analysis = $saved_card->analysis;
            //$saved_card->analysis->analysis_column = $saved_card->analysis->analysis_columns;
        }

        return response($saved_cards)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function saveCard(Request $request){
        $user = $request->get('user');

        $card = new saved_card();
        $card->analysis_id = $request->get('id');
        $card->user_uuid = $user->uuid;
        $card->position = saved_card::where('user_uuid', $user->uuid)->count();
        $card->size = $request->get('size');
        $card->displayed = null != $request->get('displayed') ? $request->get('displayed') : false;

        $card->save();

        return response($card)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function updateCard(Request $request){
        $user = $request->get('user');

        $card = saved_card::where('uuid', $user->uuid)->where('id', $request->get('id'))->first();
        $card->id = $request->get('id');
        $card->position = $request->get('position');
        $card->size = $request->get('size');
        $card->displayed = null != $request->get('displayed') ? $request->get('displayed') : $card->displayed;

        $card->save();

        return response($card)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function deleteCard(Request $request){
        $user = $request->get('user');

        $card = saved_card::where('uuid', $user->uuid);
        $card->delete();
    }
}
