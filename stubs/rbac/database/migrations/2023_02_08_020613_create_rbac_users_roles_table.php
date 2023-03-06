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
        Schema::create('rbac_users_roles', function (Blueprint $table) {
            $table->id()->comment('ID');
            $table->bigInteger('user_id')->comment('用户ID');
            $table->bigInteger('role_id')->comment('角色ID');
            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rbac_users_roles');
    }
};
