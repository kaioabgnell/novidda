<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Guarda nome do usuário e da empresa (quando enviados pelo widget) para a listagem "quem reagiu".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('emoji');
        });
    }

    public function down(): void
    {
        Schema::table('reactions', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
