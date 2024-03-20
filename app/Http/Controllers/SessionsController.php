<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Group;
use App\Models\PlayerGroup;
use App\Models\Player;
use App\Models\PlayerDevice;
use DB;

class SessionsController extends Controller
{
    public function index()
    {
        $sessions = Session::all(); 
        return response()->json($sessions, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        //validar dados recebidos
        $validatedData = $request->validate([
            'session_time' => 'required',
            'phoneNumbers.*' => ['nullable', 'string', 'distinct'],
        ]);

        

        //Query para obter todos os jogadores que estão a jogar, a query vai garantir que nenhum jogador inserido já esteja numa sessão
        $playersInActiveSession = DB::table('players')
            ->join('player_devices', 'players.id' , '=' , 'player_devices.player_id')
            ->where('is_active', '=' , 1)
            ->orderBy('players.id')
            ->get();

            foreach ($validatedData['phoneNumbers'] as $phoneNumber) {
                foreach ($playersInActiveSession as $player) {
                    if ($player->phoneNumber === $phoneNumber) {
                        return response()->json(['error' => 'One of the players is already in an active session'], 400);
                    }
                }
            }

        //Atribuir o player_id aos phoneNumbers recebidos
        $playerIds = [];
        foreach ($validatedData['phoneNumbers'] as $phoneNumber) {
            if ($phoneNumber === null) {
                continue;
            }
            $player = Player::where('phoneNumber', $phoneNumber)->first();
            if ($player) {
                $playerIds[] = $player->id;
            }else {
                // Caso um dos número não exista
                return response()->json(['error' => 'One or more phoneNumber don\'t exist'], 404);
            }
    

        }

        //Não deve chegar aqui
        if (empty($playerIds)) {
            return response()->json(['error' => 'We found no players with that phone number'], 404);
        }

        //query que fornece todos os devices existente que não estejam em uso
        $devicesAvailable = DB::table('devices')
        ->leftJoin('player_devices', function($join) {
            $join->on('devices.id', '=', 'player_devices.device_id')
                ->where('player_devices.is_active', '=', 1);
        })
        ->whereNull('player_devices.device_id')
        ->orderBy('devices.id')
        ->pluck('devices.id')
        ->toArray();

        //Caso não haja nenhum device
        if (empty($devicesAvailable)) {
            return response()->json(['error' => 'No devices available'], 404);
        }

        //Caso não haja device para o grupo todo
        if (count($playerIds) > count($devicesAvailable)) {
            return response()->json(['error' => 'Insufficient devices available'], 404);
        }

        //criar grupo
        $group = new Group([]);

        $group->save(); 

        //criar session
        $sessions = new Session([
            'group_id' => $group->id,
            'session_time' => $validatedData['session_time'],
        ]);
        $sessions->save(); 

        foreach($playerIds as $playerId){
            if (!empty($devicesAvailable)) {
                //Primeiro device do array
                $deviceId = array_shift($devicesAvailable);

                /*
                Random Device
                
                $randomDevice = array_rand($devicesAvailable);
                $deviceId = $devicesAvailable[$randomDevice];
                unset($devicesAvailable[$randomDevice]);
                */


                // Atribuir device ao player
                $playerDevice = new PlayerDevice([
                    'player_id' => $playerId,
                    'device_id' => $deviceId,
                ]);
                $playerDevice->save();

                // Atribuir grupo ao player
                $playerGroup = new PlayerGroup([
                    'player_id' => $playerId,
                    'group_id' => $group->id,
                ]);
                $playerGroup->save();
            } else {
                //Não deve chegar aqui
                return response()->json(['error' => 'No devices available'], 404);
            }
        }

        



        

        return response()->json($group->id, Response::HTTP_CREATED); 

        

    }


    public function sessionFinish(Request $request){
        $validatedData = $request->validate([
            'group_id' => 'required',
        ]);

        $groupId = $validatedData['group_id'];
        
        //Desliga a session
        Session::where('group_id', $groupId)
        ->update(['is_active' => 0]);

        //Desliga todos os devices ativos por membros desse grupo
        $playerInGroup = DB::table('players')
            ->join('player_devices', 'players.id' , '=' , 'player_devices.player_id')
            ->join('player_groups', 'players.id' , '=' , 'player_groups.player_id')
            ->where('group_id','=', $groupId)
            ->update(['is_active' => 0]);





        return response()->json($groupId, Response::HTTP_OK); 

    }

}
