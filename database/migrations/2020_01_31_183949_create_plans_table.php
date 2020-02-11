<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->increments('id');
            $table->char('display_name', 100);
            $table->char('code', 100);
            $table->string('description')->nullable();
            $table->unsignedInteger('type_id');
            $table->foreign('type_id')->references('id')->on('plan_types');
            $table->string('metadata')->nullable();
            $table->tinyInteger('is_visible')->default(1);
            $table->timestamps();
            $table->softDeletes()->nullable();	
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
