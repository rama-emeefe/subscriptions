<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanTypeFeatureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.plan_type_feature'), function(Blueprint $table)
		{
			$table->integer('type_id')->unsigned();
			$table->integer('feature_id')->unsigned()->index('fk_plan_type_feat_features_idx');
			$table->primary(['type_id','feature_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('subscriptions.tables.plan_type_feature'));
	}

}
