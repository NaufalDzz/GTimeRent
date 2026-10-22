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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 120)->index();
            $table->string('kind', 20);
            $table->text('specs_text');
            $table->text('contents_text');
            $table->unsignedBigInteger('package_price');
            $table->unsignedBigInteger('daily_fine');
            $table->string('condition_status', 20)->default('ready');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('category_unit', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->primary(['category_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_unit');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};
