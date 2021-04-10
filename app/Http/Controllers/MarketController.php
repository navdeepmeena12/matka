<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\MarketType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function provideMarketNames()
    {
        return response()->json(Market::all());
    }

    public function provideMarketDigits(Request $request)
    {
        $market_id = $request->market_id;
        $market = MarketType::where('market_id', $market_id)->first();
        $date = Carbon::now()->format('j-F-Y');

        $digits = null;
        if ($market_id == 1) {
            $digits = $this->singleDigits();
        } else if ($market_id == 2) {
            $digits = $this->doubleDigits();
        } else if ($market_id == 3) {
            $digits = $this->spDigits();
        } else if ($market_id == 4) {
            $digits = $this->dpDigits();
        } else {
            $digits = $this->tpDigits();
        }

        $response = [
            "market_data" => $market,
            'market_numbers' => $digits,
            "date_today" => $date
        ];
        return response($response, 200);


    }

    private function singleDigits()
    {
        return range(0, 9);
    }

    private function doubleDigits()
    {
        return [
            00,
            01,
            02,
            03,
            04,
            05,
            06,
            07,
            "08",
            "09",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "20",
            "21",
            "22",
            "23",
            "24",
            "25",
            "26",
            "27",
            "28",
            "29",
            "30",
            "31",
            "32",
            "33",
            "34",
            "35",
            "36",
            "37",
            "38",
            "39",
            "40",
            "41",
            "42",
            "43",
            "44",
            "45",
            "46",
            "47",
            "48",
            "49",
            "50",
            "51",
            "52",
            "53",
            "54",
            "55",
            "56",
            "57",
            "58",
            "59",
            "60",
            "61",
            "62",
            "63",
            "64",
            "65",
            "66",
            "67",
            "68",
            "69",
            "70",
            "71",
            "72",
            "73",
            "74",
            "75",
            "76",
            "77",
            "78",
            "79",
            "80",
            "81",
            "82",
            "83",
            "84",
            "85",
            "86",
            "87",
            "88",
            "89",
            "90",
            "91",
            "92",
            "93",
            "93",
            "95",
            "96",
            "97",
            "98",
            "99"
        ];

    }

    private function spDigits()
    {
        return [
            469, 234, 450, 270,
            379,
            180,
            568,
            360,
            135,
            478,
            289,
            126,
            459,
            260,
            378,
            189,
            369,
            170,
            567,
            350,
            134,
            468,
            279,
            125,
            458,
            269,
            368,
            250,
            359,
            179,
            890,
            340,
            160,
            467,
            278,
            124,
            367,
            240,
            358,
            178,
            349,
            169,
            790,
            268,
            150,
            457,
            259,
            123,
            456,
            249,
            357,
            230,
            348,
            168,
            780,
            267,
            159,
            690,
            258,
            140,
            590,
            239,
            356,
            167,
            347,
            158,
            789,
            257,
            149,
            680,
            248,
            130,
            580,
            238,
            490,
            157,
            346,
            148,
            689,
            256,
            139,
            670,
            247,
            120,
            570,
            237,
            480,
            156,
            390,
            147,
            679,
            345,
            138,
            589,
            246,
            129,
            560,
            245,
            489,
            236,
            470,
            146,
            678,
            380,
            137,
            579,
            290,
            128,
            479,
            280,
            460,
            190,
            389,
            145,
            578,
            370,
            136,
            569,
            235,
            127
        ];
    }

    private function dpDigits()
    {
        return [
            100,
            110,
            112,
            113,
            114,
            115,
            116,
            117,
            118,
            119,
            122,
            133,
            144,
            155,
            166,
            177,
            188,
            199,
            200,
            220,
            223,
            224,
            225,
            226,
            227,
            228,
            229,
            233,
            244,
            255,
            266,
            277,
            288,
            299,
            300,
            330,
            334,
            335,
            336,
            337,
            338,
            339,
            344,
            355,
            366,
            377,
            388,
            399,
            400,
            440,
            445,
            446,
            447,
            448,
            449,
            455,
            466,
            477,
            488,
            499,
            500,
            550,
            556,
            557,
            558,
            559,
            566,
            577,
            588,
            599,
            600,
            660,
            667,
            668,
            669,
            677,
            688,
            699,
            700,
            770,
            778,
            779,
            788,
            799,
            800,
            880,
            889,
            899,
            900,
            990
        ];
    }

    private function tpDigits()
    {
        return [
            000,
            111,
            222,
            333,
            444,
            555,
            666,
            777,
            888,
            999
        ];
    }
    
    public function provideOneMarketData(Request $request)
    {
        $marketId = $request->market_id;
        $data = Market::where('market_id', $marketId)->first();
        return response($data, 200);

    }

    public function setMarketResult(Request $request)
    {
        $result = $request->result;
        $result_type = $request->result_type;
        $market_id = $request->market_id;
        try {
            if ($result_type == "open") {
                $market = Market::where("market_id", $market_id)
                    ->where('open_pana', '***')
                    ->first();
                $market->open_market_status = 0;
                $market->open_pana = $result;
                $market->save();
            } else if ($result_type == "close") {
                $market = Market::where('market_id', $market_id)
                    ->where('open_pana', '!=', "***")
                    ->where('close_pana', "***")
                    ->first();
                $market->close_market_status = 0;
                $market->market_status = 0;
                $market->close_pana = $result;
                $market->save();
            }
        } catch (\Exception $exception) {
            return response()
                ->json([
                    "message" => $exception->getMessage()
                ]);
        }

    }


}

