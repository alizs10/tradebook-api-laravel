<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatisticValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistic_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistic_id')->constrained('statistics')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->text('value');
            $table->timestamps();
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
        Schema::dropIfExists('statistic_values');
    }
}
