<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketsTable extends Migration
{
    public function up()
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer("market_id");
            $table->string("market_name");
            $table->string("market_result");
            $table->boolean("open_market_status");
            $table->boolean("close_market_status");
            $table->boolean("market_status");
            $table->dateTime("market_close_time");
            $table->dateTime("market_open_time");
        });
    }

    public function down()
    {
        Schema::dropIfExists('markets');
    }
}
