<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanTypeFetaureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plan_type_fetaure', function(Blueprint $table)
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
		Schema::drop('plan_type_fetaure');
	}

}
