<?php

namespace App\Http\Controllers;

use App\Models\GaliBet;
use App\Models\GaliMarket;
use App\Models\Point;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use function PHPUnit\Framework\throwException;

class GaliController extends Controller
{
    public function gali()
    {
        $gali = DB::table('gali_markets')->get();
        return $gali;
    }
    
      public function chart_url(Request $request)
    {
        try{
        $chart = DB::table('chart_urls')->where('market_id', $request->id)->value('chart_url');
         
        return response()->json(['status' => 200,
        'url' => $chart]);
        
        }catch(Exception $exception) {
               
        return response()->json(['status' => 400,
        'url' => null]);
        }
    

    }


    public function currentDate()
    {
        $date = Carbon::now()->format('j-F-Y');
        return response()->json(['date' => $date]);
    }

    public function bid_place(Request $request)
    {
        $options = [
            'user_id' => $request->user_id,
            'bet_digit' => $request->bid_digit,
            'bet_amount' => $request->bid_amount,
            'gali_id' => $request->gali_id,
            'bet_date' => $request->bid_date,
            'bet_rate' => 90,
        ];

        try {
            GaliBet::create($options);
            $this->settleUserPoints($request->bid_amount, $request->user_id);
            return response()->json([
                'message' => 'Bid placed successfully'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Unable to place bid'
            ]);
        }
    }

    public function settleBid()
    {
        $bid = $this->getUserBid();
        $result = $this->market_result($bid['gali_id']);
        try {
            if (!empty($result)) {
                if ($bid['bet_digit'] == $result['result']) {
                    $this->initUserPointsTransaction($bid['user_id'], $bid['bet_amount'], $bid['bet_rate']);
                    $bid->is_win = 1;
                    $bid->save();
                } else {
                    $bid->is_win = 0;
                    $bid->save();
                }
            } else {
                echo "Market is not closed yet";
            }

        } catch (Exception $exception) {
            throwException($exception);
        }
    }

    public function getUserBid()
    {
        $date = Carbon::now()->format('j-F-Y');
        $bids = GaliBet::where('is_win', null)
            ->where("bet_date", $date)
            ->first();
        return $bids;
    }

    private function market_result($market_id)
    {
        $market_info = GaliMarket::where("id", $market_id)
            ->where("result", "!=", "**")
            ->where("is_closed", 0)
            ->first();
        return $market_info;
    }

    private function initUserPointsTransaction($user_id, $bet_amount, $bet_rate)
    {
        $points_to_be_added = $bet_amount * $bet_rate;
        $user_wallet = Point::where('user_id', $user_id)->first();
        $user_current_points = $user_wallet['points'];
        $user_wallet->points = $points_to_be_added + $user_current_points;
        $user_wallet->save();
    }

    public function bidHistory(Request $request)
    {
        return response()->json(GaliBet::where("user_id", $request->user_id)->get());
    }


    public function winHistory(Request $request)
    {
        $wins = GaliBet::where("user_id", $request->user_id)->
        where("is_win", 1)->get();
        return response()->json($wins);
    }
    
     public function settleUserPoints($bet_amount, $user_id)
    {
        $points_on_server = Point::where('user_id', $user_id)->first();
        $user_points = $points_on_server->points;
        $after_points = $user_points - $bet_amount;
        $points_on_server->points = $after_points;
        $points_on_server->save();


    }
    
       public function closeMarket()
    {
        try {
            date_default_timezone_set('Asia/Kolkata');
            $time = date("H:i", time());
            $markets = GaliMarket::all();
            foreach ($markets as $item) {
                if ($item['open_time'] <= $time) {
                    $m = GaliMarket::where('id', $item['id'])->first();
                    $m->is_closed = 0;
                    $m->save();
                }
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
    
    public function resetMarkets()
    {
        try {
            $markets = GaliMarket::all();
            foreach ($markets as $item) {
                $m = GaliMarket::where('id', $item['id'])->first();
                $m->is_closed = 1;
                $m->result = "**";
                $m->save();
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
