<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Group;
use App\Models\PlayerGroup;
use App\Models\Player;
use App\Models\Device;
use App\Models\PlayerDevice;
use Carbon\Carbon;
use Carbon\CarbonInterval;
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
            'devices.*' => ['nullable', 'string', 'distinct'],
        ]);
        
        $startTime = now();

        list($hours, $minutes, $seconds) = sscanf($validatedData['session_time'], "%d:%d:%d");
        $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
        $endTime = Carbon::parse($startTime)->addSeconds($totalSeconds);

        //Query para obter todos os jogadores que estão a jogar, a query vai garantir que nenhum jogador inserido já esteja numa sessão
        $playersInActiveSession = DB::table('players')
            ->join('player_devices', 'players.id' , '=' , 'player_devices.player_id')
            ->where('is_active', '=' , 1)
            ->orderBy('players.id')
            ->get();

            if (count($validatedData['phoneNumbers']) !== count($validatedData['devices'])) {
                return response()->json(['error' => 'Not the same number of phoneNumber and devices'], 400);
            }

            
            foreach ($validatedData['phoneNumbers'] as $phoneNumber) {
                foreach ($playersInActiveSession as $player) {
                    if ($player->phoneNumber === $phoneNumber) {
                        return response()->json(['error' => 'One of the players is already in an active session'], 400);
                    }
                }
            }

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


            $devicesInUse = Device::select('devices.serialNumber')
            ->join('player_devices', 'devices.id', '=', 'player_devices.device_id')
            ->where('player_devices.is_active', 1)
            ->distinct()
            ->get();

            foreach ($validatedData['devices'] as $serialNumber) {
                foreach ($devicesInUse as $device) {
                    if ($device->serialNumber === $serialNumber) {
                        return response()->json(['error' => 'One of the devices is already in an active session'], 400);
                    }
                }
            }



            foreach ($validatedData['devices'] as $device) {
                if ($device === null) {
                    continue;
                }
                
                $deviceModel = Device::where('serialNumber', $device)->first();
                if (!$deviceModel) {
                    return response()->json(['error' => 'One or more devices don\'t exist'], 404);
                }
                if ($device) {
                    $deviceID[] = $deviceModel->id;
                }else {
                    // Caso um dos número não exista
                    return response()->json(['error' => 'One or more devices don\'t exist'], 404);
                }
            }

            //criar grupo
        $group = new Group([]);

        $group->save(); 

        //criar session
        $sessions = new Session([
            'group_id' => $group->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
        $sessions->save(); 

        foreach ($validatedData['devices'] as $index => $device) {
            if ($device === null) {
                continue;
            }
            
            $deviceModel = Device::where('serialNumber', $device)->first();
            if (!$deviceModel) {
                return response()->json(['error' => 'One or more devices don\'t exist'], 404);
            }
            $deviceId = $deviceModel->id;
        
            // Dá o playerID
            $playerId = $playerIds[$index];
            
            // Cria na tabela PlayerDevie
            $playerDevice = new PlayerDevice([
                'player_id' => $playerId,
                'device_id' => $deviceId,
            ]);
            $playerDevice->save();
        
            // Cria na tabela PlayerGroup
            $playerGroup = new PlayerGroup([
                'player_id' => $playerId,
                'group_id' => $group->id,
            ]);
            $playerGroup->save();
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
