<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('changelog_contextual_banners', function (Blueprint $table) {
            $table->boolean('countdown_enabled')->default(false)->after('cta_new_tab');
            $table->timestamp('countdown_target_at')->nullable()->after('countdown_enabled');
            $table->enum('title_align', ['left', 'center', 'right'])->default('left')->after('countdown_target_at');
            $table->enum('description_align', ['left', 'center', 'right'])->default('left')->after('title_align');
        });
    }

    public function down(): void
    {
        Schema::table('changelog_contextual_banners', function (Blueprint $table) {
            $table->dropColumn(['countdown_enabled', 'countdown_target_at', 'title_align', 'description_align']);
        });
    }
};
