<?php


    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;


    return new class extends Migration {
        public function up()
        {
            Schema::create('system_pools', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('total_collected')->default(0);
                $table->timestamp('last_jackpot_at')->nullable();
                $table->timestamps();
            });


            // seed one row
            DB::table('system_pools')->insert(['total_collected' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }


        public function down()
        {
         Schema::dropIfExists('system_pools');
        }
    };
