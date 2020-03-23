<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlansTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('subscriptions.tables.plans'), function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('display_name', 100);
			$table->string('code', 100);
			$table->text('description', 65535)->nullable();
			$table->integer('plan_type_id')->unsigned()->index('fk_plans_plan_types_idx');
			$table->text('metadata', 65535)->nullable();
			$table->boolean('is_visible')->nullable()->default(1);
			$table->boolean('is_default')->default(0);
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['code','plan_type_id'], 'fk_plans_type_code');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop(config('subscriptions.tables.plans'));
	}

}
