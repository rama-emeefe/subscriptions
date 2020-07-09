<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlanTypeFeatureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(config('emeefe.subscriptions.tables.plan_type_feature'), function(Blueprint $table)
		{
			$table->foreign('feature_id', 'fk_plan_type_feat_features')->references('id')->on('plan_features')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('type_id', 'fk_plan_type_feat_types')->references('id')->on('plan_types')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(config('emeefe.subscriptions.tables.plan_type_feature'), function(Blueprint $table)
		{
			$table->dropForeign('fk_plan_type_feat_features');
			$table->dropForeign('fk_plan_type_feat_types');
		});
	}

}
