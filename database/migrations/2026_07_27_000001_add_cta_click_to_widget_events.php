<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Adiciona o tipo de evento de clique no botão de ação (CTA) do changelog.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment','feedback','contextual_shown','contextual_dismissed','contextual_clicked','cta_click') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE widget_events MODIFY type ENUM('open','view','reaction','comment','feedback','contextual_shown','contextual_dismissed','contextual_clicked') NOT NULL");
    }
};
