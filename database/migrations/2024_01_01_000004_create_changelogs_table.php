<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('description')->nullable(); // HTML sanitizado vindo do Quill
            $table->enum('type', ['feature', 'hotfix', 'improvement', 'announcement'])->default('feature');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('reaction_emoji', 16)->default('❤️');
            // published_at no futuro = agendamento; job publica na hora marcada.
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'slug']);
            $table->index(['account_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }
};
