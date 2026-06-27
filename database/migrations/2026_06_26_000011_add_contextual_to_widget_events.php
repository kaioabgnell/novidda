<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment','feedback','contextual_shown','contextual_dismissed','contextual_clicked') NOT NULL");

        Schema::table('widget_events', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('reader_id');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment','feedback') NOT NULL");

        Schema::table('widget_events', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
