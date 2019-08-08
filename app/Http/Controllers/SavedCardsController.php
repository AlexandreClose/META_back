<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\saved_card;

class SavedCardsController extends Controller
{
    public function getAllSavedCards(Request $request){
        $user = $request->get('user');
        $saved_cards = saved_card::where('uuid', $user->uuid)->get();

        return response($saved_cards)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }

    public function saveCard(Request $request){
        $user = $request->get('user');

        $card = new saved_card();
        $card->id = $request->get('id');
        $card->uuid = $user->uuid;
        $card->position = $request->get('position');
        $card->size = $request->get('size');

        $card->save();

        return response($card)->header('Content-Type', 'application/json')->header('charset', 'utf-8');
    }
}
