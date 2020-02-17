<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPlanPeriodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('plan_periods', function(Blueprint $table)
		{
			$table->foreign('plan_id', 'fk_plan_periods_plans')->references('id')->on('plans')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('plan_periods', function(Blueprint $table)
		{
			$table->dropForeign('fk_plan_periods_plans');
		});
	}

}
