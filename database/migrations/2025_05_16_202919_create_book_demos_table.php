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
        Schema::create('book_demos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('phone_number', 15);
            $table->string('email', 100)->nullalbe();
            $table->boolean('demo_status')->default(0)->comment("1 for Finished and 0 for Pending");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_demos');
    }
};
