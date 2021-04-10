<?php

namespace App\Http\Controllers;

use App\Models\MarketType;
use App\Models\Point;
use App\Models\User;
use App\Models\UserBids;
use Illuminate\Http\Request;

class UserBidController extends Controller
{
    public function getUserBids(Request $request)
    {
        $userId = $request->user_id;
        return User::find($userId)->userBids->all();

    }

    public function hashPassword(Request $request)
    {
        return bcrypt($request->password);
    }

      public function placeUserBid(Request $request)
    {
        $user_points = $this->userPoints($request->user_id);

        $market_type_id = $this->marketTypeId($request->bet_type);

        $bet_rate = MarketType::where('market_id', $market_type_id)->first()->market_rate;

        $bidOptions = [
            'user_id' => $request->user_id,
            'bet_type' => $request->bet_type,
            'bet_digit' => $request->bet_digit,
            'bet_amount' => $request->bet_amount,
            'market_id' => $request->market_id,
            'bet_date' => $request->bet_date,
            'bet_rate' => $bet_rate,
            'market_session' => $request->market_session
        ];

        // $market_status = Market::where('market_id', $request['market_id'])->first();
        // $market_status_error = [
        //     'error_msg' => 'Market Already Closed Cannot Place Bid',
        //     'code' => 406
        // ];
        try {
            // if (strtolower($request['market_session']) == 'open' && $market_status['open_market_status'] == 0) {
            //     return response()->json($market_status_error);
            // } else if ($market_status['open_market_status'] == 0 && $request['bet_type'] == "double") {
            //     return response()->json($market_status_error);
            // } else if ($market_status['open_market_status'] == 0 && $request['bet_type'] == "half_sangam") {
            //     return response()->json($market_status_error);
            // } else if ($market_status['open_market_status'] == 0 && $request['bet_type'] == "full_sangam") {
            //     return response()->json($market_status_error);
            // } else if (strtolower($request['market_session']) == 'close' && $market_status['close_market_status'] == 0) {
            //     return response()->json($market_status_error);
            // }
            if ($user_points >= $request->bet_amount) {
                UserBids::create($bidOptions);
                $this->settleUserPoints($request->bet_amount, $request->user_id);
                return response()->json([
                    'error_msg' => 'Bet Placed Successfully',
                    "response" => 200
                ]);
            } else {
                return response()->json([
                    'error_msg' => 'Insufficient Points',
                    "response" => 909
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error_msg' => 'Unable to Place Bet',
                "response" => 406
            ]);
        }
    }



    private function removeDuplicateBets()
    {
        $date = date('Y-m-d H:i:s');
        $duplicate_bets = UserBids::where("created_at", $date)
            ->where("bet_digit")
            ->first();
    }

    public function userPoints($id)
    {
        return Point::where('user_id', $id)->first()->points;
    }

    private function marketTypeId($key)
    {
        if ($key == 'single') {
            return 1;
        } else if ($key == 'double') {
            return 2;
        } else if ($key == "single_panel") {
            return 3;
        } else if ($key == 'double_panel') {
            return 4;
        } else if ($key == 'triple_panel') {
            return 5;
        } else if ($key == 'half_sangam') {
            return 6;
        } else if ($key == 'full_sangam') {
            return 7;
        }
    }

    private function settleUserPoints($bet_amount, $user_id)
    {
        $points_on_server = Point::where('user_id', $user_id)->first();
        $user_points = $points_on_server->points;
        $after_points = $user_points - $bet_amount;
        $points_on_server->points = $after_points;
        $points_on_server->save();


    }
}
