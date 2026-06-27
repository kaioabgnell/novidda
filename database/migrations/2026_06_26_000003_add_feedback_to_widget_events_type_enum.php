<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment','feedback') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment') NOT NULL");
    }
};
