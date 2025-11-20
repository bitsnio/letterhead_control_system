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
            $table->string('scanned_copy')->nullable()->after('serial_number');
            $table->text('notes')->nullable()->after('scanned_copy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_usages', function (Blueprint $table) {
            $table->dropColumn(['scanned_copy', 'notes']);
        });
    }
};
