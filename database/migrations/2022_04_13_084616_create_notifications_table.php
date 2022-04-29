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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text("message");
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamp('notified_at')->nullable();
            $table->string('section');
            $table->string('type')->default("warning")->comment("success, warning, error, primary, info");
            $table->tinyInteger('seen')->default(0)->comment("0 => unseen, 1 => seen");
            $table->tinyInteger('status_code')->comment("0 => normal, 1 => no plan ,2 => plan is over, 3 => plan is about to be over, 4 => send by Admin");
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
        Schema::dropIfExists('notifications');
    }
};
