<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Player;
use App\Models\Group;
use DB;



class DevicesController extends Controller
{
    public function index()
    {
        $devices = Device::all(); 
        return response()->json($devices, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'serialNumber' => 'required',
        ]);

        $devices = new Device([
            'serialNumber' => $validatedData['serialNumber'],
        ]);

        $devices->save(); 

        return response()->json($devices, Response::HTTP_CREATED); 
    }

    public function destroy($id)
    {
        $devices = Device::find($id);

        if (!$devices) {
            return response()->json(['message' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        $devices->delete();
        return response()->json(['message' => 'Device deleted'], Response::HTTP_OK);
    }

    public function home() {
        $devices = Device::get();
        $players = Player::get();
        $groups = Group::get();

        /*$playersInGroup = DB::table('players')
            ->join('player_groups', 'player_groups.player_id', '=' , 'players.id')
            ->orderBy('group_id', 'ASC')
            ->get();
        Query Para Mostrar todos os players em todos os grupos existentes*/
        
        /*$GroupsInSession = DB::table('players')
            ->join('player_groups', 'player_groups.player_id', '=' , 'players.id')
            ->join('groups', 'groups.id', '=' , 'player_groups.group_id')
            ->join('sessions','sessions.group_id', '=', 'groups.id')
            ->where('sessions.is_active', '=' , 1)
            ->orderBy('groups.id', 'ASC')
            ->get();
        Query que mostra todos os grupos e players que tem um sessão ativa*/

        $groupsInSession = DB::table('players')
            ->join('player_groups', 'player_groups.player_id', '=' , 'players.id')
            ->join('groups', 'groups.id', '=' , 'player_groups.group_id')
            ->join('sessions','sessions.group_id', '=', 'groups.id')
            ->join('player_devices', 'players.id', '=', 'player_devices.player_id')
            ->join('devices', 'devices.id', '=', 'player_devices.device_id')
            ->where('sessions.is_active', '=' , 1)
            ->where('player_devices.is_active', '=' , 1)
            ->orderBy('groups.id', 'ASC')
            ->orderBy('players.id', 'ASC')
            ->get();



            $devicesAvailable = DB::table('devices')
            ->leftJoin('player_devices', function($join) {
                $join->on('devices.id', '=', 'player_devices.device_id')
                    ->where('player_devices.is_active', '=', 1);
            })
            ->whereNull('player_devices.device_id')
            ->orderBy('devices.id')
            ->select('devices.id')
            ->get();

            $new = Yes;
            $playersAvailable = DB::table('players')
                ->join('player_devices', 'players.id' , '=' , 'player_devices.player_id')
                ->where('is_active', '=' , 1)
                ->orderBy('players.id')
                ->select('players.id')
                ->get();

            
            
        




        



            


        return view('teste', compact('devices','players','groups','groupsInSession','devicesAvailable','playersAvailable'));
    }   
}
