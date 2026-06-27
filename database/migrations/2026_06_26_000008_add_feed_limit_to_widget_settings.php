<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->tinyInteger('feed_limit')->unsigned()->default(5)->after('roadmap_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('widget_settings', function (Blueprint $table) {
            $table->dropColumn('feed_limit');
        });
    }
};
