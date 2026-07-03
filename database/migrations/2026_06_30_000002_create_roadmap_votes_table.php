<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_item_id')->constrained()->cascadeOnDelete();
            $table->string('reader_id', 64);
            $table->enum('vote', ['up', 'down']);
            $table->timestamps();

            $table->unique(['roadmap_item_id', 'reader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_votes');
    }
};
