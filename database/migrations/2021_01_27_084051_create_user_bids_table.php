<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bids', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("user_id")->unsigned;
            $table->string("bet_type");
            $table->integer("bet_digit");
            $table->integer("market_id");
            $table->integer("bet_amount");
            $table->string("market_session");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bit');
    }
}
