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
        Schema::create('letterhead_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('current_quantity')->default(0);
            $table->integer('minimum_level')->default(0);
            $table->string('unit')->default('pieces');
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->date('last_restocked')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letterhead_inventory');
    }
};
