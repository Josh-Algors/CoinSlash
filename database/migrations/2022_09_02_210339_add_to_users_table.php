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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('phone_email_verified')->default(false);
            $table->boolean('status')->default(false);
            $table->string('gender')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('dob')->nullable();
            $table->float('weight')->nullable();
            $table->float('height')->nullable();
            $table->integer('age')->nullable();
            
            $table->foreign('category_id')->references('id')->on('user_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
