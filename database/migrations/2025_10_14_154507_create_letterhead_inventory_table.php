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
        Schema::create('letterhead_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name');
            $table->integer('start_serial');
            $table->integer('end_serial');
            $table->integer('quantity');
            $table->date('received_date');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letterhead_inventories');
    }
};
