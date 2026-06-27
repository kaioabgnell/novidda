<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// FK users.account_id -> accounts. Em migration separada porque a tabela users
// (migration default do Laravel) é criada antes de accounts.
// O SQLite não suporta adicionar FK a uma tabela existente, então pulamos nele
// (usado só nos testes); em produção (MySQL) a FK é criada normalmente.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
    }
};
