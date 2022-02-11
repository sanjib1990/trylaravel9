<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thinkifics', function (Blueprint $table) {
            $table->id();
            $table->string("subdomain", 255)->unique();
            $table->longText("token");
            $table->longText("refresh_token");
            $table->string("gid", 100)->index();
            $table->bigInteger("expires_in")->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thinkifics');
    }
};
