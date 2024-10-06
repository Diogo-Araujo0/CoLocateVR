<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\PlayerGroup;
use App\Models\Player;
use App\Models\Device;
use App\Models\PlayerDevice;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use DateTime;

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

    public function getPlayerInfo(Request $request){
        $validatedData = $request->validate([
            'deviceIdentifier' => 'required|string',
        ]);

        $last4Digits = substr($request->deviceIdentifier, -4);

        $device = Device::where('serialNumber', 'like', '%' . $last4Digits)->first();

        $playerInfo = DB::table('player_devices')
        ->join('players', 'players.id' , '=' , 'player_devices.player_id')
        ->where('device_id', '=', $device->id)
        ->where('is_active', 1)
        ->first();
        $sessionInfo = DB::table('sessions')
        ->join('groups', 'sessions.group_id', '=', 'groups.id')
        ->join('player_groups', 'player_groups.group_id', '=', 'groups.id')
        ->where('is_active',1)
        ->where('player_id', '=', $playerInfo->player_id)
        ->first();


        $endTime = new DateTime($sessionInfo->end_time);
        $currentTime = new DateTime();
        
        $interval = $currentTime->diff($endTime);
        

        $timeLeft = $interval->format('%H:%I:%S');

        if ($playerInfo) {
            return response()->json([
                'playerInfo' => $playerInfo->phoneNumber,
                'timeLeft' => $timeLeft
            ]);
        } else {
            return response()->json(['error' => 'Device not detected'], 404);
        }
    }
}
