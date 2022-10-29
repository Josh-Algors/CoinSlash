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
        Schema::create('patients_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('age')->nullable();
            $table->string('heart_rate')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('blood_pressure')->nullable();
            $table->string('glucose_level')->nullable();
            $table->string('allergies')->nullable();
            $table->string('chronic_diseases')->nullable();
            $table->string('medications')->nullable();
            $table->string('surgeries')->nullable();
            $table->string('injuries')->nullable();
            $table->string('pregnant')->nullable();
            $table->string('pre-existing_conditions')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients_profile');
    }
};
