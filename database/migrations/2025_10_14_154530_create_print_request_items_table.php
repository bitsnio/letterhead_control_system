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
        Schema::create('print_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_request_id')->constrained('print_requests')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('letterhead_templates');
            $table->integer('quantity');
            $table->integer('start_serial')->nullable();
            $table->integer('end_serial')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_request_items');
    }
};
