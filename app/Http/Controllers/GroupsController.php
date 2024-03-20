<?php

namespace App\Http\Controllers;


use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Group;

class GroupsController extends Controller
{
    public function index()
    {
        $groups = Group::all(); 
        return response()->json($groups, Response::HTTP_OK); 
    }

    public function store(Request $request)
    {
        $groups = new Group([]);

        $groups->save(); 

        return response()->json(['id' => $groups->id]); 
    }
}
