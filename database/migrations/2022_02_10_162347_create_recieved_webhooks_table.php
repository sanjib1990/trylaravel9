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
        Schema::create('recieved_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string("subdomain");
            $table->string("resource");
            $table->string("hook_id");
            $table->string("action");
            $table->json("headers");
            $table->json("webhook_data");
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
        Schema::dropIfExists('recieved_webhooks');
    }
};
