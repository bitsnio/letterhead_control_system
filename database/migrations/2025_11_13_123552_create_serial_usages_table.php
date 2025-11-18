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
        Schema::create('serial_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letterhead_inventory_id')->constrained('letterhead_inventories')->cascadeOnDelete();
            $table->foreignId('print_job_id')->constrained('print_jobs')->cascadeOnDelete();
            $table->integer('serial_number');
            $table->timestamp('used_at');
            $table->timestamps();
            
            $table->unique(['letterhead_inventory_id', 'serial_number']);
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_usages');
    }
};
