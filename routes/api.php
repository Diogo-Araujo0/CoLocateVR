<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\PlayersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\PlayersGroupsController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\PlayersDevicesController;
use App\Models\Device;
use App\Models\Player;
use App\Models\User;
use App\Models\Group;
use App\Models\PlayerGroup;
use App\Models\Session;
use App\Models\PlayerDevice;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login']);

Route::post('getPlayerInfo', [PlayersGroupsController::class, 'getPlayerInfo']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    //Routes for devices
    Route::get('devices/get', [DevicesController::class, 'index']);

    Route::post('devices/post', [DevicesController::class, 'store']);

    Route::delete('devices/delete/{id}', [DevicesController::class, 'destroy']); 

    //Routes for users
    Route::get('players/get', [PlayersController::class, 'index']);

    Route::post('players/post', [PlayersController::class, 'store']);

    Route::delete('players/delete/{id}', [PlayersController::class, 'destroy']); 

    //Routes for groups
    Route::get('groups/get', [GroupsController::class, 'index']);

    Route::post('groups/post', [GroupsController::class, 'store']);

    //Routes for playerGroup
    Route::get('playerGroups/get', [PlayersGroupsController::class, 'index']);

    Route::post('playerGroups/post', [PlayersGroupsController::class, 'store']);

    //Routes for sessions
    Route::get('sessions/get', [SessionsController::class, 'index']);

    Route::post('sessions/post', [SessionsController::class, 'store']);

    Route::post('sessions/sessionFinish', [SessionsController::class, 'sessionFinish']);

    //Routes for playerDevices
    Route::get('playerDevices/get', [PlayersDevicesController::class, 'index']);

    Route::post('playerDevices/post', [PlayersDevicesController::class, 'store']);
});

