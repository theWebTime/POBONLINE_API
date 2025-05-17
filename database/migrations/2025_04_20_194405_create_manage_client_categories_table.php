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
        Schema::create('manage_client_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_function_id');
            $table->foreign('client_function_id')->references('id')->on('client_functions');
            $table->unsignedBigInteger('category_management_id');
            $table->foreign('category_management_id')->references('id')->on('category_management');
            $table->string('category_quantity', 50);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_client_categories');
    }
};
