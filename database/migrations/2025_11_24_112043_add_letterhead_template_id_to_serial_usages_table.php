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
        Schema::table('serial_usages', function (Blueprint $table) {
            $table->foreignId('letterhead_template_id')
                ->nullable()
                ->constrained('letterhead_templates')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_usages', function (Blueprint $table) {
            $table->dropForeign(['letterhead_template_id']);
            $table->dropColumn('letterhead_template_id');
        });
    }
};
