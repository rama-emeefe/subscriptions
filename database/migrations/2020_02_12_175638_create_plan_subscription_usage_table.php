<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanSubscriptionUsageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plan_subscription_usage', function(Blueprint $table)
		{
			$table->integer('feature_id')->unsigned();
			$table->integer('subscription_id')->unsigned()->index('fk_subscription_feature_subscription_idx');
			$table->integer('limit')->nullable();
			$table->integer('usage')->nullable();
			$table->primary(['feature_id','subscription_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('plan_subscription_usage');
	}

}
