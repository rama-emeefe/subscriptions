<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlanSubscriptionUsageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('plan_subscription_usage', function(Blueprint $table)
		{
			$table->foreign('feature_id', 'fk_subscription_feature_feature')->references('id')->on('plan_features')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('subscription_id', 'fk_subscription_feature_subscription')->references('id')->on('plan_subscriptions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('plan_subscription_usage', function(Blueprint $table)
		{
			$table->dropForeign('fk_subscription_feature_feature');
			$table->dropForeign('fk_subscription_feature_subscription');
		});
	}

}
