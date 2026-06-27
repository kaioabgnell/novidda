<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Controle de não-lidos por leitor anônimo.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('reader_id', 64);
            $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();

            $table->unique(['reader_id', 'changelog_id']);
            $table->index(['account_id', 'reader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reads');
    }
};
