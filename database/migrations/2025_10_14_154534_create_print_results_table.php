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
        Schema::create('print_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_request_id')->constrained('print_requests');
            $table->foreignId('template_id')->constrained('letterhead_templates');
            $table->integer('requested_quantity');
            $table->integer('successful_prints');
            $table->integer('wasted_prints')->default(0);
            $table->text('wastage_reason')->nullable();
            $table->foreignId('printed_by')->constrained('users');
            $table->timestamp('printed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_results');
    }
};
