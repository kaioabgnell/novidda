<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('changelog_widget_settings', function (Blueprint $table) {
            $table->boolean('feedback_enabled')->default(false)->after('show_reactions');
        });
    }

    public function down(): void
    {
        Schema::table('changelog_widget_settings', function (Blueprint $table) {
            $table->dropColumn('feedback_enabled');
        });
    }
};
