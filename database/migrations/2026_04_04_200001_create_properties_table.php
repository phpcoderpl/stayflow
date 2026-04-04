<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type')->default('apartment');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country')->default('PL');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description_pl')->nullable();
            $table->text('description_en')->nullable();
            $table->integer('max_guests')->default(4);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->integer('area_sqm')->nullable();
            $table->integer('base_price_per_night');
            $table->string('currency')->default('PLN');
            $table->integer('cleaning_fee')->default(0);
            $table->string('check_in_time')->default('15:00');
            $table->string('check_out_time')->default('11:00');
            $table->integer('min_nights')->default(1);
            $table->boolean('reservations_enabled')->default(true);
            $table->string('video_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
