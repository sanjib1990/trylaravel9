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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string("external_order_id")->default("")->index();
            $table->string("student_email")->index();
            $table->string("student_id")->index();
            $table->string("course_id")->index();
            $table->string("course_name")->index();
            $table->string("product_id");
            $table->string("amount");
            $table->string("currency");
            $table->string("provider")->index();
            $table->string("order_type")->index();
            $table->string("action")->index();
            $table->string("status")->index();
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
        Schema::dropIfExists('orders');
    }
};
