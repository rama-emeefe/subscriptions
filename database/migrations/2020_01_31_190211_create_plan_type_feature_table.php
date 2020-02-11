<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanTypeFeatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_type_feature', function (Blueprint $table) {
            $table->unsignedInteger('type_id');
            $table->foreign('type_id')->references('id')->on('plan_types');
            $table->unsignedInteger('feature_id');
            $table->foreign('feature_id')->references('id')->on('plan_features');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_type_feature');
    }
}
