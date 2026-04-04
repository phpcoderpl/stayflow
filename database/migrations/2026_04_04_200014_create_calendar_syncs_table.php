<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('direction')->default('import');
            $table->string('provider')->default('ical');
            $table->string('ical_url')->nullable();
            $table->string('ical_export_token')->unique()->nullable();
            $table->string('google_calendar_id')->nullable();
            $table->json('google_tokens')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('sync_errors')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_syncs');
    }
};
