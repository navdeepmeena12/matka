<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function userList()
    {
        try {
            return response()->json([
                "error" => false,
                "message" => "Users Fetched Successfully",
                "data" => User::all()
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                "error" => true,
                "data" => $exception,
                "message" => "Unable to Fetch Users"
            ], 401);
        }


    }


}
