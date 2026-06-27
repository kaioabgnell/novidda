<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_item_id')->constrained()->cascadeOnDelete();
            $table->string('reader_id', 64);
            $table->string('author_name')->nullable();
            $table->text('body');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['roadmap_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_comments');
    }
};
