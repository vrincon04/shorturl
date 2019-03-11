<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('link_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('link_id')->unsigned();
            $table->timestamps();
            $table->foreign('link_id')
                ->references('id')
                ->on('links')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_histories');
    }
}
