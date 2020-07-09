<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanFeatureValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('emeefe.subscriptions.tables.plan_feature_values'), function(Blueprint $table)
		{
			$table->integer('plan_id')->unsigned();
			$table->integer('plan_feature_id')->unsigned()->index('fk_plan_feature_plan_features_idx');
			$table->integer('limit')->unsigned()->nullable()->default(null);
			$table->primary(['plan_id','plan_feature_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('emeefe.subscriptions.tables.plan_feature_values'));
	}

}
