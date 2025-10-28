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
        Schema::table('print_results', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('status_notes')->nullable();
            $table->timestamp('status_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_results', function (Blueprint $table) {
            $table->dropColumn(['status', 'assigned_to', 'status_notes', 'status_updated_at']);
        });
    }
};
