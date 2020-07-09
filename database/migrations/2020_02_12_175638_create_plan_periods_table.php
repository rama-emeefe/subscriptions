<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanPeriodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('emeefe.subscriptions.tables.plan_periods'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('display_name', 100);
			$table->string('code', 100);
			$table->float('price', 10, 0)->default(0);
			$table->string('currency', 3)->default('MXN')->comment('Currency ISO 4217');
			$table->integer('plan_id')->unsigned()->nullable()->index('fk_plan_periods_plans_idx');
			$table->integer('trial_days')->default(0);
			$table->enum('period_unit', array('day','month','year'))->nullable()->comment('Si es null no es recurrente');
			$table->integer('period_count')->nullable()->comment('Si es null no es recurrente');
			$table->boolean('is_recurring')->default(0);
			$table->boolean('is_visible')->nullable()->default(1);
			$table->integer('tolerance_days')->default(0);
			$table->timestamps();
			$table->boolean('is_default')->default(0);
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('emeefe.subscriptions.tables.plan_periods'));
	}

}
