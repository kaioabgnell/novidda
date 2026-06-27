<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->constrained()->cascadeOnDelete();
            // reader_id anônimo (localStorage/cookie) gerado no cliente.
            $table->string('reader_id', 64);
            $table->string('emoji', 16);
            $table->timestamps();

            // Um leitor reage uma vez por changelog.
            $table->unique(['changelog_id', 'reader_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
