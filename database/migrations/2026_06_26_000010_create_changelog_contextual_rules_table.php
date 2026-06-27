<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_contextual_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')
                ->constrained('changelog_contextual_banners')
                ->cascadeOnDelete();
            $table->enum('type', ['include', 'exclude']);
            $table->enum('match_mode', ['exact', 'contains', 'starts_with', 'regex']);
            $table->string('pattern', 500);
            $table->timestamps();

            $table->index(['banner_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_contextual_rules');
    }
};
