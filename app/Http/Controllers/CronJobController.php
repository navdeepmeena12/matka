<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Point;
use App\Models\UserBids;
use Carbon\Carbon;
use Exception;
use function PHPUnit\Framework\throwException;

class CronJobController extends Controller
{

    public function initCheckSingle()
    {
        $this->settleSingleBetsClose();
        $this->settleSingleBetsOpen();
    }



    public function settleSingleBetsOpen()
    {
        $date = Carbon::now()->format('j-F-Y');
        $bids = UserBids::where("bet_type", 'single')
            ->where("is_win", null)
            ->where('market_session', 'open')
            ->where('bet_date', $date)
            ->limit(10)
            ->get();

        foreach ($bids as $bid) {
            $result = DB::table('markets')
                ->where('market_id', $bid['market_id'])
                ->where('open_pana', '!=', "***")
                ->value('open_pana');

            if (!empty($result)) {
                if ($bid['bet_digit'] == $this->lastDigit($this->sum($result))) {
                    $this->initUserPointsTransaction($bid['user_id'], $bid['bet_amount'], $bid['bet_rate']);
                    $bid->is_win = true;
                } else {
                    $bid->is_win = false;
                }
                $bid->save();
            }
        }
    }

    public function settleSingleBetsClose()
    {
        $date = Carbon::now()->format('j-F-Y');
        $bids = UserBids::where("bet_type", 'single')
            ->where("is_win", null)
            ->where('market_session', 'close')
            ->where('bet_date', $date)
            ->limit(10)
            ->get();

        foreach ($bids as $bid) {
            $result = DB::table('markets')
                ->where('market_id', $bid['market_id'])
                ->where('close_pana', '!=', "***")
                ->value('close_pana');

            if (!empty($result)) {
                if ($bid['bet_digit'] == $this->lastDigit($this->sum($result))) {
                    $this->initUserPointsTransaction($bid['user_id'], $bid['bet_amount'], $bid['bet_rate']);
                    $bid->is_win = true;
                } else {
                    $bid->is_win = false;
                }
                $bid->save();
            }
        }
    }


    public function settleSingleClose()
    {
        $close_single = $this->provideSingleBets("close");
        try {
            $market_result = $this->provideMarketResult($close_single["market_id"], 2);
            $close_result = $this->lastDigit($this->sum($market_result));
            if ($close_single->bet_digit == $close_result) {
                $this->initUserPointsTransaction($close_single['user_id'], $close_single['bet_amount'], $close_single['bet_rate']);
                $close_single->is_win = 1;
                $close_single->save();
            } else {
                $close_single->is_win = 0;
                $close_single->save();
            }

        } catch (Exception $exception) {
            throwException($exception);
        }
    }

    private function provideSingleBets($type)
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", "single")
            ->where("market_session", $type)
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->first();
    }

    private function provideMarketResult($market_id, $type = 3)
    {
        $o_market_status = 0;
        $c_market_status = 0;
        if ($type == 1) {
            $o_market_status = 0;
            $c_market_status = 1;
        }
        $market = Market::where("market_id", $market_id)
            ->where("open_market_status", $o_market_status)
            ->where("close_market_status", $c_market_status)
            ->first();
        if ($type == 1) {
            return $market->open_pana;
        } else if ($type == 2) {
            return $market->close_pana;
        } else if ($type == 3) {
            return [$market->open_pana, $market->close_pana];
        }
    }

    private function lastDigit($number)
    {
        $arr = str_split($number);
        return end($arr);
    }

    private function sum($number)
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

    private function initUserPointsTransaction($user_id, $bet_amount, $bet_rate)
    {
        $points_to_be_added = $bet_amount * $bet_rate;
        $user_wallet = Point::where('user_id', $user_id)->first();
        $user_current_points = $user_wallet['points'];
        $user_wallet->points = $points_to_be_added + $user_current_points;
        $user_wallet->save();
    }

    public function settleSingleOpen()
    {
        $open_single = $this->provideSingleBets("open");
        try {
            $market_result = $this->provideMarketResult($open_single["market_id"], 1);
            $open_result = $this->lastDigit($this->sum($market_result));
            if ($open_single->bet_digit == $open_result) {
                $this->initUserPointsTransaction($open_single['user_id'], $open_single['bet_amount'], $open_single['bet_rate']);
                $open_single->is_win = 1;
                $open_single->save();
            } else {
                $open_single->is_win = 0;
                $open_single->save();
            }
        } catch (Exception $exception) {
            throwException($exception);
        }

    }

    public function initCheckDouble()
    {
        $this->settleDoubleBets();
    }

    // public function settleDoubleBets()
    // {
    //     $double_bets = $this->provideDoubleBets();
    //     try {
    //         $market_result = $this->provideMarketResult($double_bets["market_id"]);
    //         $sum_of_open = $this->lastDigit($this->sum($market_result[0]));
    //         $sum_of_close = $this->lastDigit($this->sum($market_result[1]));
    //         $total_result = $sum_of_open . $sum_of_close;
    //         if ($double_bets->bet_digit == $total_result) {
    //             $this->initUserPointsTransaction($double_bets['user_id'], $double_bets['bet_amount'], $double_bets['bet_rate']);
    //             $double_bets->is_win = 1;
    //             $double_bets->save();
    //             return response()->json([
    //                 'message' => "bet won",
    //                 'bet' => $double_bets,
    //                 'total_result' => $total_result
    //             ]);
    //         } else {
    //             $double_bets->is_win = 0;
    //             $double_bets->save();
    //             return response()->json([
    //                 'message' => "bet lost",
    //                 'bet' => $double_bets,
    //                 'total_result' => $total_result
    //             ]);
    //         }
    //     } catch (Exception $exception) {
    //         return response()->json([
    //             'message' => $exception->getMessage()

    //         ]);
    //     }

    // }
    
    public function settleDoubleBets()
    {
        $double_bets = $this->provideDoubleBets();
        try {
            $market_result = $this->provideMarketResultForDoubleBets($double_bets["market_id"]);
            if ($double_bets->bet_digit == $market_result) {
                $this->initUserPointsTransaction(
                    $double_bets['user_id'],
                    $double_bets['bet_amount'],
                    $double_bets['bet_rate']
                );
                $double_bets->is_win = true;
            } else {
                $double_bets->is_win = false;
            }
            $double_bets->save();
        } catch (Exception $exception) {
            throwException($exception);
        }

    }
    
    private function provideMarketResultForDoubleBets($market_id)
    {
        $market = Market::where('market_id', $market_id)
            ->where('market_status', 0)
            ->first();

        $open = $this->sum($market['open_pana']);
        $close = $this->sum($market['close_pana']);

        return $this->lastDigit($open) . $this->lastDigit($close);
    }



    private function provideDoubleBets()
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", "double")
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->where("market_session", "null")
            ->first();
    }

    public function initCheckSP()
    {
        $this->settleSinglePanelOpen();
        $this->settleSinglePanelClose();
    }

    private function settleSinglePanelOpen()
    {
        $single_panel_open = $this->providePanelOpen('single_panel');
        try {
            $market_result = $this->provideMarketResult($single_panel_open['market_id'], 1);
            if ($single_panel_open->bet_digit == $market_result) {
                $this->initUserPointsTransaction($single_panel_open['user_id'], $single_panel_open['bet_amount'], $single_panel_open['bet_rate']);
                $single_panel_open->is_win = 1;
                $single_panel_open->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $single_panel_open,
                    'total_result' => $market_result
                ]);
            } else {
                $single_panel_open->is_win = 0;
                $single_panel_open->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $single_panel_open,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    private function providePanelOpen($type)
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", $type)
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->where("market_session", "Open")
            ->first();
    }

    private function settleSinglePanelClose()
    {
        $close_panel_open = $this->providePanelClose("single_panel");
        try {
            $market_result = $this->provideMarketResult($close_panel_open['market_id'], 2);
            if ($this->checkEqual($close_panel_open->bet_digit, $market_result)) {
                $this->initUserPointsTransaction($close_panel_open['user_id'], $close_panel_open['bet_amount'], $close_panel_open['bet_rate']);
                $close_panel_open->is_win = 1;
                $close_panel_open->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $close_panel_open,
                    'total_result' => $market_result
                ]);
            } else {
                $close_panel_open->is_win = 0;
                $close_panel_open->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $close_panel_open,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }


    }

    private function providePanelClose($type)
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", $type)
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->where("market_session", "close")
            ->first();
    }

    private function checkEqual($first, $second)
    {
        if ($first == $second) {
            return true;
        } else {
            return false;
        }
    }

    public function initCheckDP()
    {
        $this->settleDoublePanelOpen();
        $this->settleDoublePanelClose();
    }

    private function settleDoublePanelOpen()
    {
        $bets = $this->providePanelOpen("double_panel");
        try {
            $market_result = $this->provideMarketResult($bets['market_id'], 1);
            if ($bets->bet_digit == $market_result) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    private function settleDoublePanelClose()
    {
        $bets = $this->providePanelClose("double_panel");
        try {
            $market_result = $this->provideMarketResult($bets['market_id'], 2);
            if ($bets->bet_digit == $market_result) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    public function initCheckTP()
    {
        $this->settleTriplePanelOpen();
        $this->settleTriplePanelClose();
    }

    private function settleTriplePanelOpen()
    {
        $bets = $this->providePanelOpen("triple_panel");
        try {
            $market_result = $this->provideMarketResult($bets['market_id'], 1);
            if ($bets->bet_digit == $market_result) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    private function settleTriplePanelClose()
    {
        $bets = $this->providePanelClose("triple_panel");
        try {
            $market_result = $this->provideMarketResult($bets['market_id'], 2);
            if ($bets->bet_digit == $market_result) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    public function initResetMarket()
    {
        try {
            $market = Market::all();
            foreach ($market as $item) {
                $item->open_pana = "***";
                $item->close_pana = "***";
                $item->open_market_status = 1;
                $item->close_market_status = 1;
                $item->market_status = 1;
                $item->save();
            }
        } catch (Exception $exception) {

        }
    }

    public function initCheckHsg()
    {
        $this->initCheckHalfSangamBetsOpen();
        $this->initCheckHalfSangamBetsClose();
        $this->initCheckFullSangamBets();
    }

    public function initCheckHalfSangamBetsOpen()
    {
        $bets = $this->getHalfSangamBets("open");
        $bet_digit = $bets['bet_digit'];
        $open_panel = substr($bet_digit, 0, 3);
        $close_digit = substr($bet_digit, 3, 4);
        try {
            $market_result = $this->provideResultHalfSangamOpen($bets['market_id']);
            $market_open_panel = $market_result[0];
            $market_close_digit = $this->sum($market_result[1]);
            if ($open_panel == $market_open_panel && $close_digit == $market_close_digit) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    public function getHalfSangamBets($type)
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", "half_sangam")
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->where("market_session", $type)
            ->first();
    }

    private function provideResultHalfSangamOpen($market_id)
    {
        $o_market_status = 0;
        $c_market_status = 0;
        $market = Market::where("market_id", $market_id)
            ->where("open_market_status", $o_market_status)
            ->where("close_market_status", $c_market_status)
            ->first();
        return [$market->open_pana, $market->close_pana];
    }

    public function initCheckHalfSangamBetsClose()
    {
        $bets = $this->getHalfSangamBets("close");
        $bet_digit = $bets['bet_digit'];
        $close_panel = substr($bet_digit, 1, 3);
        $open_digit = substr($bet_digit, 0, 1);
        try {
            $market_result = $this->provideMarketResult($bets['market_id'], 1);
            $market_close_panel = $market_result[0];
            $market_open_digit = $this->sum($market_result[1]);
            if ($close_panel == $market_close_panel && $open_digit == $market_open_digit) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    public function initCheckFullSangamBets()
    {
        $bets = $this->getFullSangamBets();
        $open_panel = substr($bets['bet_digit'], 0, 3);
        $close_panel = substr($bets['bet_digit'], 3, 6);
        try {
            $market_result = $this->provideMarketResultFullSangam($bets['market_id']);
            $market_open_panel = $market_result[0];
            $market_close_panel = $market_result[1];
            if ($open_panel == $market_open_panel && $close_panel == $market_close_panel) {
                $this->initUserPointsTransaction($bets['user_id'], $bets['bet_amount'], $bets['bet_rate']);
                $bets->is_win = 1;
                $bets->save();
                return response()->json([
                    'message' => "bet won",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            } else {
                $bets->is_win = 0;
                $bets->save();
                return response()->json([
                    'message' => "bet lost",
                    'bet' => $bets,
                    'total_result' => $market_result
                ]);
            }
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'bet' => null,
                'total_result' => null
            ]);
        }
    }

    private function getFullSangamBets()
    {
        $date = Carbon::now()->format('j-F-Y');
        return UserBids::where("bet_type", "full_sangam")
            ->where("is_win", null)
            ->where("bet_date", $date)
            ->where("market_session", "null")
            ->first();
    }

    private function provideMarketResultFullSangam($market_id)
    {
        $o_market_status = 0;
        $c_market_status = 0;
        $market = Market::where("market_id", $market_id)
            ->where("open_market_status", $o_market_status)
            ->where("close_market_status", $c_market_status)
            ->first();
        return [$market->open_pana, $market->close_pana];
    }

    public function initMarketStatusChangeOpen()
    {
        try {
            date_default_timezone_set('Asia/Kolkata');
            $time = date("H:i", time());
            $markets = Market::all();
            foreach ($markets as $item) {
                if ($item['market_open_time'] == $time) {
                    $m = Market::where('id', $item['id'])->first();
                    $m->open_market_status = 0;
                    $m->save();
                }
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function initMarketStatusChangeClose()
    {
        try {
            date_default_timezone_set('Asia/Kolkata');
            $time = date("H:i", time());
            $markets = Market::all();
            foreach ($markets as $item) {
                if ($item['market_close_time'] == $time) {
                    $m = Market::where('id', $item['id'])->first();
                    $m->close_market_status = 0;
                    $m->market_status = 0;
                    $m->save();
                }
            }
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
