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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 20);
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 20);
            $table->text('delivery_address');
            $table->string('service_area', 30);
            $table->unsignedBigInteger('shipping_fee_snapshot');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('initial_total');
            $table->string('payment_method', 10)->nullable();
            $table->foreignId('payment_confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('payment_confirmed_at')->nullable();
            $table->dateTime('dispatched_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('received_at')->nullable();
            $table->dateTime('receipt_recorded_at')->nullable();
            $table->foreignId('receipt_recorded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->dateTime('return_recorded_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('return_notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('code_snapshot', 30);
            $table->string('name_snapshot', 120);
            $table->text('contents_snapshot');
            $table->unsignedBigInteger('package_price_snapshot');
            $table->unsignedBigInteger('daily_fine_snapshot');
            $table->unsignedInteger('late_days')->default(0);
            $table->unsignedBigInteger('fine_amount')->default(0);
            $table->timestamps();

            $table->unique(['rental_id', 'unit_id']);
            $table->index(['unit_id', 'rental_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flat_shipping_fee');
            $table->text('service_area_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('rental_items');
        Schema::dropIfExists('rentals');
    }
};
