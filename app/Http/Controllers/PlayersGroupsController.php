<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\PlayerGroup;
use App\Models\Player;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PlayersGroupsController extends Controller
{
    public function index()
    {
        $playerGroup = PlayerGroup::all(); 
        return response()->json($playerGroup, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'phoneNumber' => 'required',
                'group_id' => 'required',
            ]);
            $player = Player::where('phoneNumber', $validatedData['phoneNumber'])->first();

            if (!$player) {
                return response()->json(['error' => 'Player not found'], 404);
            }

            $playerGroup = new PlayerGroup([
                'player_id' => $player->id,
                'group_id' => $validatedData['group_id'],
            ]);

            $playerGroup->save(); 

            return response()->json($playerGroup, Response::HTTP_CREATED); 
        }
        catch (QueryException $e) {
            return response()->json(['error' => 'The player is already on the group.'], Response::HTTP_CONFLICT);
        }
    }
}
