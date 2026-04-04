<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_id')->constrained();
            $table->string('confirmation_code')->unique();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('nights');
            $table->integer('guests_count')->default(1);
            $table->string('status')->default('pending');
            $table->string('source')->default('website');
            $table->integer('base_total')->default(0);
            $table->integer('cleaning_fee')->default(0);
            $table->integer('discount_amount')->default(0);
            $table->integer('total_price')->default(0);
            $table->string('currency')->default('PLN');
            $table->integer('deposit_amount')->default(0);
            $table->boolean('deposit_paid')->default(false);
            $table->text('special_requests')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('guest_id');
            $table->index('status');
            $table->index('check_in');
            $table->index('check_out');
            $table->index('confirmation_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
