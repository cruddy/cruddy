<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddImagesToCategoriesTableAndImageToProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('products', function (Blueprint $table) {
            $table->string('image');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->text('images');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('images');
        });
	}

}