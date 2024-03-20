<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Player;

class PlayersController extends Controller
{
    public function index()
    {
        $players = Player::all(); 
        return response()->json($players, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'phoneNumber' => 'required',
        ]);
        $player = Player::where('phoneNumber', $validatedData['phoneNumber'])->first();

        if($player){
            return response()->json(['error' => 'Phone number already exists'], 404);
        }else{
            $players = new Player([
                'phoneNumber' => $validatedData['phoneNumber'],
            ]);
    
            $players->save(); 
        }

       

        return response()->json($players, Response::HTTP_CREATED); 
    }

    public function destroy($id)
    {
        $players = Player::find($id);

        if (!$devices) {
            return response()->json(['message' => 'Player not found'], Response::HTTP_NOT_FOUND);
        }

        $players->delete();
        return response()->json(['message' => 'Player deleted'], Response::HTTP_OK);
    }
}
