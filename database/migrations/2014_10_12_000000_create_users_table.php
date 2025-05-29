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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone_number', 15);
            $table->string('studio_name', 100);
            $table->string('image')->nullable();
            $table->string('address');
            $table->string('email', 100);
            $table->string('password');
            $table->smallInteger('role')->default(2)->comment('1 for Super Admin 2 for Admin');
            $table->boolean('status')->default(0)->comment("1 for active and 0 for in-active");
            $table->string('subscription_date');
            $table->string('subscription_end_date')->nullabe();
            $table->string('instagram_link')->nullabe();
            $table->string('facebook_link')->nullabe();
            $table->string('youtube_link')->nullabe();
            $table->string('website_link')->nullabe();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
