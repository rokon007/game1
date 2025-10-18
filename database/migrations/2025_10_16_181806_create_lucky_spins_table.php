<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up()
    {
        Schema::create('lucky_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('bet_amount');
            $table->enum('result',['win','lose','jackpot']);
            $table->unsignedBigInteger('reward_amount')->default(0);
            $table->unsignedBigInteger('system_pool_before')->default(0);
            $table->unsignedBigInteger('system_pool_after')->default(0);
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('lucky_spins');
    }
};
