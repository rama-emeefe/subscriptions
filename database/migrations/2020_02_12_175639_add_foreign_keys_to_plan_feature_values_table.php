<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlanFeatureValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(config('subscriptions.tables.plan_feature_values'), function(Blueprint $table)
		{
			$table->foreign('plan_feature_id', 'fk_plan_feature_plan_features')->references('id')->on('plan_features')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('plan_id', 'fk_plan_feature_plans')->references('id')->on('plans')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(config('subscriptions.tables.plan_feature_values'), function(Blueprint $table)
		{
			$table->dropForeign('fk_plan_feature_plan_features');
			$table->dropForeign('fk_plan_feature_plans');
		});
	}

}
