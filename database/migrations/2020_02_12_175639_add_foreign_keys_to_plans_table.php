<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlansTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table(config('subscriptions.tables.plans'), function(Blueprint $table)
		{
			$table->foreign('type_id', 'fk_plans_plan_types')->references('id')->on('plan_types')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table(config('subscriptions.tables.plans'), function(Blueprint $table)
		{
			$table->dropForeign('fk_plans_plan_types');
		});
	}

}
