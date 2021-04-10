<?php

namespace App\Http\Controllers;

use App\Models\Point;
use App\Models\StarLine;
use App\Models\StarlineBets;
use App\Models\StarLineRate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StarLineController extends Controller
{
    public function provideStarLineMarkets()
    {
        return StarLine::all();
    }

    public function provideStarLineRates()
    {
        return StarLineRate::all();
    }

    public function provideStarLineBetHistory(Request $request)
    {
        return StarlineBets::where("user_id", $request->user_id)->get();
    }

    public function provideStarWinHistory(Request $request)
    {
        return StarlineBets::where("user_id", $request->user_id)
            ->where("is_win", 1)
            ->get();
    }

    public function provideCurrentDate()
    {
        return response()->json(Carbon::now()->format('j-F-Y'));
    }

    public function placeStarLineBid(Request $request)
    {
        $bet_rate = StarLineRate::where('id', $request->bet_type)->first()->star_rate;
        $bidOptions = [
            'user_id' => $request->user_id,
            'bet_type' => $request->bet_type,
            'bet_digit' => $request->bet_digit,
            'bet_amount' => $request->bet_amount,
            'market_id' => $request->market_id,
            'bet_date' => $request->bet_date,
            'bet_rate' => $bet_rate,
            'bet_time' => Carbon\Carbon::now()
        ];
        try {
            StarlineBets::create($bidOptions);
            $this->settleUserPoints($request->bet_amount, $request->user_id);
            return response()->json([
                "message" => "bet placed"
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                "message" => $exception->getMessage()
            ], 401);
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


   
   
 public function initCheckUserSingleBets()
    {
        $current_date = Carbon::now()->format('j-F-Y');
        $single_bets = StarlineBets::where("is_win", null)
            ->where("bet_date", $current_date)
            ->where("bet_type", 1)
            ->first();

        if ($single_bets != null) {
            $market = StarLine::find($single_bets['market_id']);
            if ($market['open_panel'] != '***' && $market['status'] != 1) {
                $sum = $this->sum($market['open_panel']);
                $market_result = $this->lastDigit($sum);
                if ($single_bets['bet_digit'] == $market_result) {
                    $this->initUserPointsTransaction($single_bets['user_id'], $single_bets['bet_amount'], $single_bets['bet_rate']);
                    $single_bets->is_win = 1;
                    $single_bets->save();
                    return response()->json([
                        'message' => 'bet won',
                        'market result' => $market_result,
                        'bet digit' => $single_bets['bet_digit']
                    ]);
                } else {
                    $single_bets->is_win = 0;
                    $single_bets->save();
                    return response()->json([
                        'message' => 'bet lost',
                        'market result' => $market_result,
                        'bet digit' => $single_bets['bet_digit']
                    ]);
                }
            }
        } else {
            return response()->json([
                'message' => 'No Bets Found',
                'market result' => null,
                'bet digit' => null
            ], 200);
        }
    }


  private function lastDigit($number)
    {
        $arr = str_split($number);
        return end($arr);
    }

   
  public function initCheckUserPanelBets()

    {
        $current_date = Carbon::now()->format('j-F-Y');
        $panel_bets = StarlineBets::where("is_win", null)
            ->where("bet_date", $current_date)
            ->where("bet_type", '!=', 1)
            ->first();

        if ($panel_bets != null) {
            $market = StarLine::find($panel_bets['market_id']);
            if ($market['open_panel'] != '***' && $market['status'] != 1) {
                if ($panel_bets['bet_digit'] == $market['open_panel']) {
                    
                    $this->initUserPointsTransaction($panel_bets['user_id'],
                        $panel_bets['bet_amount'],
                        $panel_bets['bet_rate']);

                    $panel_bets->is_win = 1;
                    $panel_bets->save();
                    return response()->json([
                        'message' => 'bet won',
                        'market result' => $market['open_panel'],
                        'bet digit' => $panel_bets['bet_digit']
                    ]);
                } else {
                    $panel_bets->is_win = 0;
                    $panel_bets->save();
                    return response()->json([
                        'message' => 'bet lost',
                        'market result' => $market['open_panel'],
                        'bet digit' => $panel_bets['bet_digit']
                    ]);
                }
            }
        } else {
            return response()->json([
                'message' => 'bet won',
                'market result' => null,
                'bet digit' => null
            ]);
        }
    }

    private
    function initUserPointsTransaction($user_id, $bet_amount, $bet_rate)
    {
        try {
            $points_to_be_added = $bet_amount * $bet_rate;
        $user_wallet = Point::where('user_id', $user_id)->first();
        $user_current_points = $user_wallet['points'];
        $user_wallet->points = $points_to_be_added + $user_current_points;
        $user_wallet->save(); 
            
        }catch(Exception $exception) {
            return response()->json(['invalid'],200);
        }
       
    }

    private
    function sum($number)
    {
        $num = intval($number);
        $sum = 0;
        for ($i = 0; $i <= strlen($num); $i++) {
            $rem = $num % 10;
            $sum = $sum + $rem;
            $num = $num / 10;
        }
        return $sum;
    }

  public function initStarlineClose()
    {
        try {
            date_default_timezone_set('Asia/Kolkata');
            $time = date("H:i", time());
            $market = StarLine::where('close_time', $time)
                ->where('status', 1)
                ->first();
            if ($market != null) {
                $market->status = 0;
                $market->save();
                return response()->json([
                    'message' => "Market Closed"
                ], 200);
            } else {
                return response()->json([
                    'message' => "No Closing Markets Found"
                ], 200);
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
    
      public function initResetStarlineMarkets()
    {
        try {
            $starlines = StarLine::all();
            foreach ($starlines as $starline) {
                $starline = StarLine::where('id', $starline['id'])->first();
                $starline->open_panel = '***';
                $starline->status = 1;
                $starline->save();
            }
            return response()->json([
                'message' => "market_reset"
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 200);
        }

    }

}
