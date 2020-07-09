<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlanSubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(config('emeefe.subscriptions.tables.plan_subscriptions'), function(Blueprint $table)
		{
			$table->foreign('period_id', 'fk_plan_subscriptions_plan_periods')->references('id')->on('plan_periods')->onUpdate('CASCADE')->onDelete('RESTRICT');
			$table->foreign('plan_type_id', 'fk_plan_subscriptions_plan_types')->references('id')->on('plan_types')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(config('emeefe.subscriptions.tables.plan_subscriptions'), function(Blueprint $table)
		{
			$table->dropForeign('fk_plan_subscriptions_plan_periods');
			$table->dropForeign('fk_plan_subscriptions_plan_types');
		});
	}

}
