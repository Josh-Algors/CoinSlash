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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reference')->nullable();
            $table->string('amount')->nullable();
            $table->string('balance')->nullable();
            $table->string('activity')->nullable();
            $table->string('status')->nullable();
            $table->string('description')->nullable();
            $table->string('recipient_account_number')->nullable();
            $table->string('recipient_bank_name')->nullable();
            $table->string('recipient_account_name')->nullable();
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
        Schema::dropIfExists('transaction_logs');
    }
};
