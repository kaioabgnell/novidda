<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_attribute_index', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('reader_id', 64);
            $table->json('attributes_snapshot');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['account_id', 'reader_id']);
            $table->index(['account_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_attribute_index');
    }
};
