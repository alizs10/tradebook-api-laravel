<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('pair_id')->constrained('pairs')->onUpdate('cascade')->onDelete('cascade');
            $table->tinyInteger('contract_type')->comment('0 => buy, 1 => sell');
            $table->tinyInteger('status')->default(0);
            $table->unsignedDecimal('entry_price', 10, 4);
            $table->unsignedDecimal('exit_price', 10, 4);
            $table->Integer('leverage')->default(1);
            $table->unsignedDecimal('margin', 20, 4);
            $table->decimal('pnl', 10, 4)->nullable();
            $table->decimal('profit', 10, 4)->nullable();
            $table->timestamp('trade_date')->nullable();
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
        Schema::dropIfExists('trades');
    }
}
