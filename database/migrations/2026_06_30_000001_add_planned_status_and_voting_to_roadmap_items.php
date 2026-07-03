<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE roadmap_items MODIFY status ENUM('analyzing', 'developing', 'planned') NOT NULL DEFAULT 'analyzing'");

        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->boolean('voting_enabled')->default(false)->after('feedback_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->dropColumn('voting_enabled');
        });

        DB::statement("UPDATE roadmap_items SET status = 'analyzing' WHERE status = 'planned'");
        DB::statement("ALTER TABLE roadmap_items MODIFY status ENUM('analyzing', 'developing') NOT NULL DEFAULT 'analyzing'");
    }
};
