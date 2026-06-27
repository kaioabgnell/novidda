<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Configurações de widget por changelog (sobrepõem padrões da conta).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('fire_webhook')->default(false);
            $table->boolean('show_comments')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('show_reactions')->default(true);
            // CTA exibido ao final do changelog no widget.
            $table->string('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta_color', 30)->nullable();
            $table->boolean('cta_new_tab')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_widget_settings');
    }
};
