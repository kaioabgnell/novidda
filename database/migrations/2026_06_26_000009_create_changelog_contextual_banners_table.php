<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_contextual_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changelog_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->enum('style', ['toast', 'top_bar', 'bottom_bar'])->default('toast');
            $table->enum('position', ['bottom_right', 'bottom_left', 'top_right', 'top_left'])->default('bottom_right');
            $table->enum('frequency', ['once_per_user', 'until_clicked', 'times_capped'])->default('once_per_user');
            $table->unsignedSmallInteger('frequency_cap')->nullable();
            $table->unsignedSmallInteger('auto_dismiss_seconds')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('custom_copy', 500)->nullable();
            $table->string('cta_text', 80)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->boolean('cta_new_tab')->default(false);
            $table->timestamps();

            $table->index(['enabled', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_contextual_banners');
    }
};
