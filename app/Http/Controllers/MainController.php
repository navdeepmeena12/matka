<?php

namespace App\Http\Controllers;

use App\Models\AppConfig;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function provideServerStatus()
    {
        return response()->json([
            'server_online' => true
        ], 200);
    }

    public function provideBannerImage()
    {
        $config = AppConfig::first();

        return response()->json([
            'image_url' => $config->banner_image
        ], 200);
    }

    public function provideAppConfig()
    {
        return response()->json(AppConfig::first());
    }

}
