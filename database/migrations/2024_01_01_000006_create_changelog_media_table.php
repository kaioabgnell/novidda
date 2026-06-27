<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'youtube']);
            $table->string('url')->nullable();  // URL do YouTube
            $table->string('path')->nullable(); // caminho relativo em storage/app/public para imagens
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_media');
    }
};
