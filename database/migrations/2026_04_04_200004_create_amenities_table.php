<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_pl');
            $table->string('name_en')->nullable();
            $table->string('icon')->nullable();
            $table->string('category')->default('general');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('amenity_property', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->primary(['amenity_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_property');
        Schema::dropIfExists('amenities');
    }
};
