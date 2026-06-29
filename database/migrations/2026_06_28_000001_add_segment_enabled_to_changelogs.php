<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('changelogs', function (Blueprint $table) {
            $table->boolean('segment_enabled')->default(false)->after('status');
            $table->index('segment_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('changelogs', function (Blueprint $table) {
            $table->dropIndex(['segment_enabled']);
            $table->dropColumn('segment_enabled');
        });
    }
};
