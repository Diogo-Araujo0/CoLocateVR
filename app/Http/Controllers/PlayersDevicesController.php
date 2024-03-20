<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\PlayerDevice;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PlayersDevicesController extends Controller
{
    public function index()
    {
        $playerDevice = PlayerDevice::all(); 
        return response()->json($playerDevice, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'player_id' => 'required',
                'device_id' => 'required',
            ]);


            $playerDevice = new PlayerDevice([
                'player_id' => $validatedData['player_id'],
                'device_id' => $validatedData['device_id'],
            ]);

            $playerDevice->save(); 

            return response()->json($playerDevice, Response::HTTP_CREATED); 
        }
        catch (QueryException $e) {
            return response()->json(['error' => 'Player or device not found.'], Response::HTTP_CONFLICT);
        }
    }
}
