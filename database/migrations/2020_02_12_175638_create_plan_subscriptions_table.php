<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanSubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('emeefe.subscriptions.tables.plan_subscriptions'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('period_id')->unsigned()->index('fk_plan_subscriptions_plan_periods_idx');
			$table->integer('subscriber_id')->unsigned();
			$table->string('subscriber_type');
			$table->dateTime('trial_starts_at');
			$table->dateTime('starts_at');
			$table->dateTime('expires_at')->nullable()->comment('Null si no es recurrente, si es recurrente es la fecha de inicio mas días de prueba');
			$table->dateTime('cancelled_at')->nullable()->comment('Fecha de cancelación');
			$table->string('cancellation_reason', 100)->nullable()->comment('!!Checar so lo ponemos o no');
			$table->integer('plan_type_id')->unsigned()->index('fk_plan_subscriptions_plan_types_idx');
			$table->float('price', 10, 0);
			$table->integer('tolerance_days')->default(0);
			$table->string('currency', 3)->default('MXN')->comment('Currency ISO 4217');
			$table->enum('period_unit', array('day','month','year'))->nullable()->comment('Si es null no es recurrente');
			$table->integer('period_count')->nullable()->comment('Si es null no es recurrente');
			$table->boolean('is_recurring')->default(0);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('emeefe.subscriptions.tables.plan_subscriptions'));
	}

}
