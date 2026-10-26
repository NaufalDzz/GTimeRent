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
        Schema::create('shipping_fee_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('previous_fee');
            $table->unsignedBigInteger('new_fee');
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_fee_changes');
    }
};
