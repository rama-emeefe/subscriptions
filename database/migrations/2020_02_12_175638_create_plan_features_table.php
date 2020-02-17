<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePlanFeaturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('plan_features', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('display_name', 100);
			$table->string('code', 100);
			$table->text('description', 65535)->nullable();
			$table->enum('type', array('feature','limit'))->nullable()->default('feature');
			$table->text('metadata', 65535)->nullable();
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
		Schema::drop('plan_features');
	}

}
