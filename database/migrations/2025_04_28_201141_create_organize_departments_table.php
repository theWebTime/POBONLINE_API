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
        Schema::create('organize_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('client_function_id');
            $table->foreign('client_function_id')->references('id')->on('client_functions')->onDelete('cascade');
            $table->unsignedBigInteger('manage_client_category_id');
            $table->foreign('manage_client_category_id')->references('id')->on('manage_client_categories')->onDelete('cascade');            
            $table->unsignedBigInteger('category_management_id');
            $table->foreign('category_management_id')->references('id')->on('category_management')->onDelete('cascade');
            $table->unsignedBigInteger('staff_management_id');
            $table->foreign('staff_management_id')->references('id')->on('staff_management')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organize_departments');
    }
};
