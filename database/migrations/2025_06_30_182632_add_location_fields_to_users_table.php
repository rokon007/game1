<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('users', function (Blueprint $table) {
            $table->ipAddress('last_login_ip')->nullable()->after('remember_token');
            $table->string('last_login_location')->nullable()->after('last_login_ip');
            $table->decimal('latitude', 10, 7)->nullable()->after('last_login_location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_ip', 'last_login_location', 'latitude', 'longitude']);
        });
    }
};
