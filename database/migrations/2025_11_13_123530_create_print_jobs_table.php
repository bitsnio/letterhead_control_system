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
        Schema::create('print_jobs', function (Blueprint $table) {
            
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('templates'); // Array of template IDs
            $table->json('variable_data'); // Array of template_id => variables
            $table->integer('quantity')->default(1);
            $table->integer('start_serial')->nullable();
            $table->integer('end_serial')->nullable();
            $table->foreignId('letterhead_id')->nullable()->constrained('letterhead_inventories')->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
