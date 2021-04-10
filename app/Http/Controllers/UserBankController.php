<?php

namespace App\Http\Controllers;

use App\Models\Point;
use App\Models\UserBank;
use App\Models\UserBids;
use Illuminate\Http\Request;

class UserBankController extends Controller
{

    public function provideUserBankInfo(Request $request)
    {
        try {
            $user_bank_info = UserBank::where('user_id', $request->user_id)->first();
            return response()->json([
                'success' => true,
                'error' => null,
                'info' => $user_bank_info
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
                'info' => null
            ]);
        }
    }

    public function saveUserBankInfo(Request $request)
    {
        $paytm = $request->paytm;
        $phonepe = $request->phonepe;
        $gpay = $request->gpay;
        $upi_id = $request->upi_id;
        $user_id = $request->user_id;

        $data = [
            'paytm' => $paytm,
            'phonepe' => $phonepe,
            'gpay' => $gpay,
            'upi_id' => $upi_id,
            'user_id' => $user_id
        ];

        try {
            $current_user_bank = UserBank::where('user_id', $user_id)->first();
            if (empty($current_user_bank)) {
                UserBank::create($data);
            } else {
                if ($paytm != null) {
                    $current_user_bank->paytm = $paytm;
                    $current_user_bank->save();

                } else if ($phonepe != null) {
                    $current_user_bank->phonepe = $phonepe;
                    $current_user_bank->save();

                } else if ($gpay != null) {
                    $current_user_bank->gpay = $gpay;
                    $current_user_bank->save();
                }
            }
            return response()->json([
                'error' => null,
                'success' => true
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
                'success' => false
            ]);
        }

    }

    public function userBalance(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $userBalance = Point::where('user_id', $user_id)->first()->points;
            return response()->json([
                "balance" => $userBalance
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'balance' => null
            ]);
        }


    }

    public function provideBidHistory(Request $request)
    {
        try {
            $bids = UserBids::where("user_id", $request->user_id)->get();
            return response()->json($bids);
        } catch (\Exception $exception) {
            return null;
        }

    }

    public function provideWinHistory(Request $request)
    {
        try {
            $wins = UserBids::where("user_id", $request->user_id)
                ->where('is_win', 1)
                ->get();
            return response()->json($wins);
        } catch (\Exception $exception) {
            return null;
        }
    }
}
