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
        Schema::table('letterhead_templates', function (Blueprint $table) {
            Schema::table('letterhead_templates', function (Blueprint $table) {
                $table->json('print_margins')->nullable()->after('content');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letterhead_templates', function (Blueprint $table) {
                        $table->dropColumn('print_margins');
        });
    }
};
