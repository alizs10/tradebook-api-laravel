<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onUpdate('cascade')->onDelete('cascade')->comment("null means its for all plans");
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade')->comment("null means its for all users");
            $table->integer('value')->comment("percentage");
            $table->string('code');
            $table->tinyInteger('status')->default(0)->comment("0 => unused, 1 => used");
            $table->timestamp('exp_date')->nullable()->comment('null => always usable');
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
        Schema::dropIfExists('discounts');
    }
};
