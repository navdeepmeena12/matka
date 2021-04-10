<?php

namespace App\Http\Controllers;

use App\Models\Point;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PassportAuthController extends Controller
{



    public function register(Request $request)
    {
        try {

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => bcrypt($request->password),
                'passcode' => $request->passcode
            ]);

            $token = $user->createToken('LaravelAuthApp')->accessToken;
            $user = User::where("username", $request->username)->first();
            $this->createUserBalancePoints($user->id);
            return response()->json([
                'error' => null,
                'isError' => false,
                'passcode' => $user->passcode,
                'token' => $token,
                'userId' => $user->id,
                'message' => 'User Registered Successfully',
                'user' => $user
            ], 200);


        } catch (\Exception $e) {

            $error = $e;

            return response()->json([
                'error' => $error,
                'isError' => true,
                'token' => null,
                'message' => 'Unable to Create User'
            ], 401);
        }

    }

    /**
     * Login
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        try {

            $data = [
                'username' => $request->username,
                'password' => $request->password
            ];

            if (auth()->attempt($data)) {

                $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;

                if (auth()->user()->status == 1) {
                    return response()->json([
                        'error' => Config::get("constants.account_disabled"),
                        'isError' => true,
                        'token' => null,
                        'message' => 'Your account has been disabled',
                        'user' => auth()->user()
                    ], 401);
                } else {
                    return response()->json([
                        'error' => null,
                        'isError' => false,
                        'token' => $token,
                        'message' => 'User Verified Successfully',
                        'user' => auth()->user()
                    ], 200);
                }

            } else {
                return response()->json([
                    'error' => Config::get("constants.password_wrong"),
                    'isError' => true,
                    'token' => null,
                    'message' => 'Unable to verify User'
                ], 401);
            }

        } catch (\Exception $exception) {

            return response()->json(['error' => 'error occurred'], 401);

        }

    }

    public function createUserBalancePoints($id)

    {
        try {
            $point = new Point();
            $point->user_id = $id;
            $point->points = 5;
            $point->deposit_date = null;
            $point->last_transaction = null;
            $point->save();
        } catch (\Exception $exception) {
            return null;
        }

    }

    public function provideProfile(Request $request)
    {
        try {
            $user = User::where("id", $request->id)->first();
            $points = Point::where("user_id", $request->id)->first();
            $data = [
                'username' => $user->username,
                'name' => $user->name,
                'user_id' => $user->id,
                'points' => $points->points
            ];
            return response()->json($data, 200);
        } catch (\Exception $exception) {
            return response()->json([
                'username' => $exception->getMessage(),
                'name' => null,
                'user_id' => null,
                'points' => null
            ], 312);
        }
    }

}
