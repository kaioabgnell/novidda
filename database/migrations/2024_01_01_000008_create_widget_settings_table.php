<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Configuração global do widget por conta.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('button_text')->default('Novidades');
            $table->enum('open_mode', ['side', 'dropdown'])->default('side');
            $table->enum('position', ['left', 'right'])->default('right');
            // tema: cores, fonte e preferência de modo escuro.
            $table->json('theme')->nullable();
            $table->text('custom_css')->nullable();
            $table->string('webhook_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
    }
};
