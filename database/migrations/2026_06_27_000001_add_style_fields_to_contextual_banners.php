<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('changelog_contextual_banners', function (Blueprint $table) {
            $table->string('bg_color', 20)->nullable()->after('custom_copy');
            $table->string('text_color', 20)->nullable()->after('bg_color');
            $table->text('description')->nullable()->after('text_color');
            $table->string('cta_color', 20)->nullable()->after('cta_url');
        });
    }

    public function down(): void
    {
        Schema::table('changelog_contextual_banners', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'text_color', 'description', 'cta_color']);
        });
    }
};
