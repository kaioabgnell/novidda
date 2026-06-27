<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
            $table->string('reader_id', 64);
            $table->enum('score', ['sad', 'neutral', 'happy']);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['changelog_id', 'reader_id']);
            $table->index('changelog_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_feedbacks');
    }
};
