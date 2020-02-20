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
		Schema::table('plans', function(Blueprint $table)
		{
			$table->foreign('plan_type_id', 'fk_plans_plan_types')->references('id')->on('plan_types')->onUpdate('CASCADE')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('plans', function(Blueprint $table)
		{
			$table->dropForeign('fk_plans_plan_types');
		});
	}

}
