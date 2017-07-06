<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateChangesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loggable_changes', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('model_id');
            $table->string('model_type');
            $table->enum('type', ['create', 'update', 'delete', 'restore'])->default('update');
            $table->string('set', 32)->nullable();
            $table->string('attribute')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('model_id');
            $table->index('type');
            $table->index('set');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropTable('loggable_changes');
    }

}
